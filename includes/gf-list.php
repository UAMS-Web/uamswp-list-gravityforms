<?php

declare(strict_types=1);

namespace UAMS\GF_List;

add_action('plugins_loaded', 'UAMS\GF_List\bootstrap');

/**
 * Loads the UAMSWP List All Gravity Forms
 *
 * The forms page needs a multisite network and Gravity Forms. Where either is
 * missing, this registers an admin notice saying so and loads nothing else, so
 * the plugin adds no menu and leaves every request to run as usual.
 *
 * @since 1.0.0
 */
function bootstrap(): void
{
    // Without a network there is no Network Admin to add the page to.
    if (! is_multisite()) {
        add_action('admin_notices', 'UAMS\GF_List\notice_not_multisite');

        return;
    }

    // Gravity Forms is not loaded on this request: on this site, or anywhere.
    if (! class_exists('GFAPI')) {
        add_action('network_admin_notices', 'UAMS\GF_List\notice_no_gravity_forms');

        return;
    }

    // include_once (__DIR__ . '/class.gf-block-finder.php' );
    include_once __DIR__.'/class.gf-list-functions.php';
}

/**
 * Tells an administrator of a single-site install that the plugin does nothing there.
 */
function notice_not_multisite(): void
{
    if (! current_user_can('activate_plugins')) {
        return;
    }

    echo '<div class="notice notice-error"><p>'.esc_html__('UAMSWP List All Gravity Forms needs a WordPress multisite network and does nothing on this site. Deactivate it here.', 'uamswp-list-gravityforms').'</p></div>';
}

/**
 * Tells a network administrator why the List Gravity Forms page is missing.
 */
function notice_no_gravity_forms(): void
{
    if (! current_user_can('manage_network_options')) {
        return;
    }

    echo '<div class="notice notice-error"><p>'.esc_html__('The List Gravity Forms page is unavailable because Gravity Forms is not active in Network Admin. Network-activate Gravity Forms to use it.', 'uamswp-list-gravityforms').'</p></div>';
}
