<?php

/**
 * Load includes/class.gf-list-functions.php once, under the conditions its
 * file-scope guards require.
 *
 * The file calls wp_die() unless is_multisite() is true and GFAPI exists, and it
 * registers a hook at file scope. Loaded once per process; the guards and the
 * hook run only that once, so a test that depends on the registration reads it
 * from the first load.
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

/**
 * Load the plugin's function definitions, mocking the multisite guard.
 */
function gf_list_tests_load_functions(): void
{
    when('is_multisite')->justReturn(true);

    require_once __DIR__.'/../../includes/class.gf-list-functions.php';
}
