<?php

/**
 * uamswp-list-gravityforms.php and includes/gf-list.php, the plugin's entry points.
 *
 * The first load of the plugin (tests/Support/load-plugin.php) pins the rest of the
 * file: the hook registrations, both guards in their failing state, the constants,
 * and the include of the functions file.
 *
 * The `exit` that follows a missing `WPINC` ends the PHP process, so no test runs it
 * in the suite's own process. A mutant that makes it run in a test ends that process,
 * and the mutation run counts the run as failed, which is how those mutants die: by the
 * process ending, not by an assertion naming them.
 */

declare(strict_types=1);

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\expect as expectCall;

beforeEach(function (): void {
    require_once __DIR__.'/../Support/load-plugin.php';
});

it('defines the plugin constants from the plugin header', function (): void {
    expect(gf_list_tests_load_plugin()['constants'])->toBe([
        'UAMS_GF_LIST_ROOT_URL' => 'https://example.test/plugin/',
        'UAMS_GF_LIST_PATH' => '/srv/plugin/',
        'UAMS_GF_LIST_VERSION' => '9.8.7',
    ]);
});

it('loads the functions file when bootstrap runs', function (): void {
    gf_list_tests_load_plugin();

    expect(function_exists('gf_list_admin_menu'))->toBeTrue()
        ->and(function_exists('get_gravity_forms_for_site'))->toBeTrue()
        ->and(function_exists('gf_list_admin_page'))->toBeTrue()
        ->and(function_exists('search_for_specific_gravity_form'))->toBeTrue();
});

it('warns instead of loading on a PHP version below 8.0', function (): void {
    gf_list_tests_load_plugin();

    $callback = null;

    expectCall('version_compare')->once()->with(PHP_VERSION, '8.0', '<')->andReturn(true);
    expectAdded('admin_notices')
        ->once()
        ->with(Mockery::on(static function (mixed $registered) use (&$callback): bool {
            $callback = $registered;

            return $registered instanceof Closure;
        }));
    expectCall('plugin_dir_url')->never();

    include dirname(__DIR__, 2).'/uamswp-list-gravityforms.php';

    if (! $callback instanceof Closure) {
        throw new RuntimeException('The version guard did not register a closure on admin_notices.');
    }

    // The notice is built when it is shown, so the translation runs only then.
    expectCall('__')
        ->once()
        ->with(
            'UAMSWP Logo List requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.',
            'uamswp-list-gravityforms'
        )
        ->andReturnFirstArg();

    ob_start();
    $callback();
    $html = (string) ob_get_clean();

    expect($html)->toBe('<div class="error"><p>UAMSWP Logo List requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.</p></div>');
});
