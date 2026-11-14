<?php

/**
 * Load the plugin the way WordPress does, once per process, and keep what that
 * load did.
 *
 * Loading runs file-scope code that cannot run twice in one process: it declares
 * functions, defines constants and registers hooks. So the first call arranges the
 * conditions the guards check, loads the plugin file, runs the bootstrap function
 * and records what happened; later calls return the same record. Each guard is
 * loaded in its failing state, with wp_die() mocked so it returns, so the guard's
 * branch and message are what the first load pins; the first test to call this
 * verifies those expectations when it tears down.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use function UAMS\GF_List\bootstrap;

/**
 * @return array{
 *     constants: array<string, mixed>,
 * }
 */
function gf_list_tests_load_plugin(): array
{
    static $loaded = null;

    if ($loaded !== null) {
        return $loaded;
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

    // The functions file, loaded by bootstrap(), with both guards failing: not multisite,
    // and no GFAPI yet. wp_die() is mocked, so it returns and the file loads anyway.
    when('is_multisite')->justReturn(false);
    expect('wp_die')->once()->with('This script must be run in a multisite installation.');
    expect('wp_die')->once()->with('Gravity Forms API not available. Make sure Gravity Forms is active.');
    expectAdded('network_admin_menu')->once()->with('gf_list_admin_menu');

    bootstrap();

    require_once __DIR__.'/GFAPI.php';
    require_once __DIR__.'/WP_Query.php';

    // Read with constant(): the plugin file defines these at run time from function calls, which
    // PHPStan does not evaluate, so a bare reference would be reported as an unknown constant.
    $loaded = [
        'constants' => [
            'UAMS_GF_LIST_ROOT_URL' => constant('UAMS_GF_LIST_ROOT_URL'),
            'UAMS_GF_LIST_PATH' => constant('UAMS_GF_LIST_PATH'),
            'UAMS_GF_LIST_VERSION' => constant('UAMS_GF_LIST_VERSION'),
        ],
    ];

    return $loaded;
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
