<?php

/**
 * gf_list_admin_menu(), with WordPress mocked.
 *
 * @see includes/class.gf-list-functions.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

beforeEach(function (): void {
    require_once __DIR__.'/../Support/load-functions.php';

    gf_list_tests_load_functions();
});

it('adds the network admin page that lists the forms', function (): void {
    Functions\expect('add_menu_page')
        ->once()
        ->with(
            'List All Gravity Forms',
            'List Gravity Forms',
            'manage_network_options',
            'gf-list',
            'gf_list_admin_page',
            'dashicons-forms'
        );

    gf_list_admin_menu();
});
