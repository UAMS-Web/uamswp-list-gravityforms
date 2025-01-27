<?php

if (!is_multisite()) {
    wp_die('This script must be run in a multisite installation.');
}
if (!class_exists('GFAPI')) {
    wp_die('Gravity Forms API not available. Make sure Gravity Forms is active.');
}
function get_gravity_forms_for_site($site_id) {
    switch_to_blog($site_id); // Change site
    $forms = GFAPI::get_forms(); // Retrieves all forms from this site
    $site_name = get_bloginfo('name'); // Name of site
	$site_url = get_bloginfo('url');
    // $form_links = '';
    $forms_details = [];
    foreach ($forms as $form) {
		// if (class_exists('Gravity_Forms_Block_Finder')) {
		// 	$finder = new Gravity_Forms_Block_Finder($form['id']);
		// 	$pages = $finder->find();
		// 	if (!empty($pages)) {
		// 		// echo '<td>';
		// 		foreach ($pages as $page => $page_title) {
		// 			$form_links .= '<a href="' . get_edit_post_link($page) . '">' . esc_html($page_title) . '</a><br>';
		// 		}
        //         // $form_links = $pages;
		// 		// echo '</td>';
		// 	} else {
		// 		$form_links = 'No pages found';
		// 	}
		// }

        $forms_details[] = [
            'site_id' => $site_id,
            'site_name' => $site_name,
			'site_url' => $site_url,
            'form_id' => $form['id'],
            'form_title' => $form['title'],
            'form_status' => $form['is_active'] ? 'Active' : 'Inactive',
			// 'form_links' => $form_links,
		];
    }
    restore_current_blog();
    return $forms_details;
}

add_action( 'network_admin_menu', 'gf_list_admin_menu' );
function gf_list_admin_menu() {
    add_menu_page(
        'List All Gravity Forms',
        'List Gravity Forms',
        'manage_network_options',
        'gf-list',
        'gf_list_admin_page',
        'dashicons-forms'
    );
}

function gf_list_admin_page() { ?>
    <div class="wrap">
        <h1>All Gravity Forms on Network</h1>
    <?php 
        $all_forms = [];
        $sites = get_sites();
        foreach ($sites as $site) {
            $site_id = $site->blog_id;
            $all_forms = array_merge($all_forms, get_gravity_forms_for_site($site_id));
        }
        if (!empty($all_forms)) {
            echo '<table border="1" cellpadding="5" cellspacing="0">';
            echo '<tr><th>Site ID</th><th>Site Name</th><th>Form ID</th><th>Form Name</th><th>Form Status</th><th>&nbsp;</th></tr>';
            foreach ($all_forms as $form) {
                echo '<tr>';
                echo '<td>' . esc_html($form['site_id']) . '</td>';
                echo '<td><a href="' . esc_html($form['site_url']) . '/wp-admin/">' . esc_html($form['site_name']) . '</a></td>';
                echo '<td>' . esc_html($form['form_id']) . '</td>';
                echo '<td>' . esc_html($form['form_title']) . '</td>';
                echo '<td>' . esc_html($form['form_status']) . '</td>';
                echo '<td><a href="' . esc_html($form['site_url']) . '/wp-admin/admin.php?page=gf_edit_forms&id='. esc_html($form['form_id']).'">View Form</a></td>';
                // echo '<td>' . esc_html($form['form_links']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'No forms found in network sites.';
        }
    ?>
    </div>
    <?php
}
