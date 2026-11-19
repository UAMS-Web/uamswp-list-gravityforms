<?php

declare(strict_types=1);
/*
 * Plugin Name:       UAMSWP List All Gravity Forms
 * Plugin URI:        https://github.com/UAMS-Web/uamswp-list-gravityforms
 * Description:       List All Gravity Forms in a WordPress Multisite Installation
 * Version:           1.1.1
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            UAMS Web Services
 * Author URI:        https://communications.uams.edu/web/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       uamswp-list-gravityforms
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    exit;
}

// The PHP floor, matching `Requires PHP:` in the header above.
if (version_compare(PHP_VERSION, '8.2', '<')) {
    add_action('admin_notices', function (): void {
        echo '<div class="notice notice-error"><p>'.esc_html__('UAMSWP List All Gravity Forms requires PHP 8.2 or later. Upgrade PHP or deactivate the plugin.', 'uamswp-list-gravityforms').'</p></div>';
    });

    return;
}

define('UAMS_GF_LIST_ROOT_URL', plugin_dir_url(__FILE__));
define('UAMS_GF_LIST_PATH', plugin_dir_path(__FILE__));
$plugin_header = get_file_data(
    __FILE__,
    [
        'version' => 'Version',
    ]
);
$plugin_version = $plugin_header['version'];
define('UAMS_GF_LIST_VERSION', $plugin_version);
include_once __DIR__.'/includes/gf-list.php';
