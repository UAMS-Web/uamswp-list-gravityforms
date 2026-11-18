<?php

/**
 * Load the plugin the way WordPress does, once per process, and keep what that
 * load did.
 *
 * Loading runs file-scope code that cannot run twice in one process: it declares
 * functions, defines constants and registers hooks. So the first call arranges the
 * conditions the guards check, loads the plugin file, runs the bootstrap function
 * and records what happened; later calls return the same record. bootstrap() runs
 * three times, once per guard state: not multisite, multisite without GFAPI, and
 * both present. What each run registered and whether it loaded the functions file
 * is recorded under `bootstrap`, and wp_die() is expected never to be called; the
 * first test to call this verifies those expectations when it tears down.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use function UAMS\GF_List\bootstrap;

/**
 * What the first load of the plugin recorded; one record per process.
 */
final class GfListTestsLoadRecord
{
    /**
     * @var array{constants: array<string, mixed>, bootstrap: array<string, array<string, bool>>}|null
     */
    public static ?array $record = null;
}

/**
 * @return array{
 *     constants: array<string, mixed>,
 *     bootstrap: array<string, array<string, bool>>,
 * }
 */
function gf_list_tests_load_plugin(): array
{
    if (GfListTestsLoadRecord::$record !== null) {
        return GfListTestsLoadRecord::$record;
    }

    $plugin_file = dirname(__DIR__, 2).'/uamswp-list-gravityforms.php';

    if (! defined('WPINC')) {
        define('WPINC', 'wp-includes');
    }

    // The plugin file: constants, and the include of includes/gf-list.php.
    when('version_compare')->justReturn(false);
    expect('plugin_dir_url')->once()->with($plugin_file)->andReturn('https://example.test/plugin/');
    expect('plugin_dir_path')->once()->with($plugin_file)->andReturn('/srv/plugin/');
    expect('get_file_data')
        ->once()
        ->with($plugin_file, ['version' => 'Version'])
        ->andReturn(['version' => '9.8.7']);
    expectAdded('plugins_loaded')->once()->with('UAMS\GF_List\bootstrap');

    include_once $plugin_file;

    // bootstrap() in each guard state. No state may stop the request.
    expect('wp_die')->never();
    $bootstrap = [];

    // Not multisite: a notice on admin_notices, and nothing else.
    when('is_multisite')->justReturn(false);
    $before = gf_list_tests_bootstrap_state();
    bootstrap();
    $bootstrap['not_multisite'] = gf_list_tests_bootstrap_added($before);

    // Multisite, but no GFAPI yet: a notice on network_admin_notices, and nothing else.
    when('is_multisite')->justReturn(true);
    $before = gf_list_tests_bootstrap_state();
    bootstrap();
    $bootstrap['no_gravity_forms'] = gf_list_tests_bootstrap_added($before);

    // Both present: the functions file loads and adds the menu.
    require_once __DIR__.'/GFAPI.php';
    require_once __DIR__.'/WP_Query.php';
    $before = gf_list_tests_bootstrap_state();
    bootstrap();
    $bootstrap['ready'] = gf_list_tests_bootstrap_added($before);

    // Read with constant(): the plugin file defines these at run time from function calls, which
    // PHPStan does not evaluate, so a bare reference would be reported as an unknown constant.
    GfListTestsLoadRecord::$record = [
        'constants' => [
            'UAMS_GF_LIST_ROOT_URL' => constant('UAMS_GF_LIST_ROOT_URL'),
            'UAMS_GF_LIST_PATH' => constant('UAMS_GF_LIST_PATH'),
            'UAMS_GF_LIST_VERSION' => constant('UAMS_GF_LIST_VERSION'),
        ],
        'bootstrap' => $bootstrap,
    ];

    return GfListTestsLoadRecord::$record;
}

/**
 * What bootstrap() has registered so far in this test, and whether it loaded the functions file.
 *
 * @return array<string, bool>
 */
function gf_list_tests_bootstrap_state(): array
{
    return [
        'not_multisite_notice' => has_action('admin_notices', 'UAMS\\GF_List\\notice_not_multisite') !== false,
        'no_gravity_forms_notice' => has_action('network_admin_notices', 'UAMS\\GF_List\\notice_no_gravity_forms') !== false,
        'menu' => has_action('network_admin_menu', 'gf_list_admin_menu') !== false,
        'functions_loaded' => function_exists('gf_list_admin_menu'),
    ];
}

/**
 * Which of gf_list_tests_bootstrap_state()'s entries became true since $before.
 *
 * Brain Monkey keeps a hook added earlier in the same test, so the state after a
 * call also holds what earlier calls added; this keeps only that call's additions.
 *
 * @param  array<string, bool>  $before  The state before the call.
 * @return array<string, bool>
 */
function gf_list_tests_bootstrap_added(array $before): array
{
    $added = [];

    foreach (gf_list_tests_bootstrap_state() as $key => $now) {
        $added[$key] = $now && ! $before[$key];
    }

    return $added;
}

/**
 * Stands in for parse_blocks(): the test passes the blocks as a JSON array.
 *
 * @return array<mixed, mixed>
 */
function gf_list_tests_parse_blocks(string $content): array
{
    $blocks = json_decode($content, true);

    if (! is_array($blocks)) {
        throw new RuntimeException('The test content is not a JSON array of blocks.');
    }

    return $blocks;
}
