<?php

/**
 * UAMS\GF_List\bootstrap() and its two notices, with WordPress mocked.
 *
 * bootstrap() runs once per guard state on the first load of the plugin
 * (tests/Support/load-plugin.php), which records what each run added: a notice,
 * the menu, the functions file. That load also expects wp_die() never to be
 * called, so no guard state stops the request.
 *
 * @see includes/gf-list.php
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\expect as expectCall;
use function UAMS\GF_List\notice_no_gravity_forms;
use function UAMS\GF_List\notice_not_multisite;

beforeEach(function (): void {
    require_once __DIR__.'/../Support/load-plugin.php';
});

it('adds only the single-site notice when the install is not multisite', function (): void {
    expect(gf_list_tests_load_plugin()['bootstrap']['not_multisite'])->toBe([
        'not_multisite_notice' => true,
        'no_gravity_forms_notice' => false,
        'menu' => false,
        'functions_loaded' => false,
    ]);
});

it('adds only the Gravity Forms notice on a network without GFAPI', function (): void {
    expect(gf_list_tests_load_plugin()['bootstrap']['no_gravity_forms'])->toBe([
        'not_multisite_notice' => false,
        'no_gravity_forms_notice' => true,
        'menu' => false,
        'functions_loaded' => false,
    ]);
});

it('loads the functions file and adds the menu on a network with GFAPI', function (): void {
    expect(gf_list_tests_load_plugin()['bootstrap']['ready'])->toBe([
        'not_multisite_notice' => false,
        'no_gravity_forms_notice' => false,
        'menu' => true,
        'functions_loaded' => true,
    ]);
});

it('tells a user who can activate plugins that a single site is not supported', function (): void {
    gf_list_tests_load_plugin();

    expectCall('current_user_can')->once()->with('activate_plugins')->andReturn(true);
    expectCall('esc_html__')
        ->once()
        ->with(
            'UAMSWP List All Gravity Forms needs a WordPress multisite network and does nothing on this site. Deactivate it here.',
            'uamswp-list-gravityforms'
        )
        ->andReturnFirstArg();

    ob_start();
    notice_not_multisite();

    expect((string) ob_get_clean())->toBe('<div class="notice notice-error"><p>UAMSWP List All Gravity Forms needs a WordPress multisite network and does nothing on this site. Deactivate it here.</p></div>');
});

it('shows the single-site notice to nobody who cannot activate plugins', function (): void {
    gf_list_tests_load_plugin();

    expectCall('current_user_can')->once()->with('activate_plugins')->andReturn(false);
    expectCall('esc_html__')->never();

    ob_start();
    notice_not_multisite();

    expect((string) ob_get_clean())->toBe('');
});

it('tells a network administrator that Gravity Forms is not active', function (): void {
    gf_list_tests_load_plugin();

    expectCall('current_user_can')->once()->with('manage_network_options')->andReturn(true);
    expectCall('esc_html__')
        ->once()
        ->with(
            'The List Gravity Forms page is unavailable because Gravity Forms is not active in Network Admin. Network-activate Gravity Forms to use it.',
            'uamswp-list-gravityforms'
        )
        ->andReturnFirstArg();

    ob_start();
    notice_no_gravity_forms();

    expect((string) ob_get_clean())->toBe('<div class="notice notice-error"><p>The List Gravity Forms page is unavailable because Gravity Forms is not active in Network Admin. Network-activate Gravity Forms to use it.</p></div>');
});

it('shows the Gravity Forms notice to nobody who cannot manage the network', function (): void {
    gf_list_tests_load_plugin();

    expectCall('current_user_can')->once()->with('manage_network_options')->andReturn(false);
    expectCall('esc_html__')->never();

    ob_start();
    notice_no_gravity_forms();

    expect((string) ob_get_clean())->toBe('');
});
