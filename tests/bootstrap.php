<?php

/**
 * Unit bootstrap.
 *
 * The Unit suite runs with no WordPress: WordPress functions are mocked per
 * test with Brain Monkey.
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Patchwork first: it can only redefine functions declared in files loaded
 * after it, so loading it before the plugin is what lets a test mock one of the
 * plugin's own functions, not only WordPress'.
 */
require_once __DIR__.'/../vendor/antecedent/patchwork/Patchwork.php';

/*
 * Plugin files open with `defined('ABSPATH') || exit;`. Define it so they load,
 * pointing at a directory that is not a WordPress install: code that requires a
 * core file through ABSPATH fails loudly here, which is the sign it needs a
 * WordPress integration suite.
 */
if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir().'/uamswp-list-gravityforms-tests-no-wordpress/');
}

/*
 * Plugin files are not loaded here. includes/class.gf-list-functions.php dies at
 * file scope outside a multisite install with Gravity Forms, and registers hooks
 * there, and Brain Monkey's add_action() exists only inside a test. Each test
 * loads it after arranging those conditions (see tests/Support/load-functions.php).
 *
 * GFAPI stands in for Gravity Forms' own class, which is not installed here.
 */
require_once __DIR__.'/Support/GFAPI.php';

// cspell:ignore ABSPATH autoloaded
