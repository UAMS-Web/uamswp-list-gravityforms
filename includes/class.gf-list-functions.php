<?php

if (! is_multisite()) {
    wp_die('This script must be run in a multisite installation.');
}
if (! class_exists('GFAPI')) {
    wp_die('Gravity Forms API not available. Make sure Gravity Forms is active.');
}
function get_gravity_forms_for_site($site_id): array
{
    switch_to_blog($site_id); // Change site
    $forms = GFAPI::get_forms(); // Retrieves all forms from this site
    $site_name = get_bloginfo('name'); // Name of site
    $site_url = get_bloginfo('url');
    $forms_details = [];
    foreach ($forms as $form) {
        $form_links = '';
        // Search through pages for form ID
        $pages = search_for_specific_gravity_form($form['id']);
        foreach ($pages as $page) {
            $form_links .= '<a href="'.get_edit_post_link($page).'">'.esc_html(get_the_title($page)).'</a><br>';
        }

        $forms_details[] = [
            'site_id' => $site_id,
            'site_name' => $site_name,
            'site_url' => $site_url,
            'form_id' => $form['id'],
            'form_title' => $form['title'],
            'form_status' => $form['is_active'] ? 'Active' : 'Inactive',
            'form_links' => $form_links ?: 'None',
        ];
    }
    restore_current_blog();

    return $forms_details;
}

add_action('network_admin_menu', 'gf_list_admin_menu');
function gf_list_admin_menu(): void
{
    add_menu_page(
        'List All Gravity Forms',
        'List Gravity Forms',
        'manage_network_options',
        'gf-list',
        'gf_list_admin_page',
        'dashicons-forms'
    );
}

function gf_list_admin_page(): void
{ ?>
    <div class="wrap">
        <h1>All Gravity Forms on Network</h1>
    <?php
        $all_forms = [];
    $sites = get_sites();
    foreach ($sites as $site) {
        $site_id = $site->blog_id;
        $all_forms = array_merge($all_forms, get_gravity_forms_for_site($site_id));
    }
    if ($all_forms !== []) {
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr><th>Site ID</th><th>Site Name</th><th>Form ID</th><th>Form Name</th><th>Form Status</th><th>&nbsp;</th><th>Pages with Form</th></tr>';
        foreach ($all_forms as $form) {
            echo '<tr>';
            echo '<td>'.esc_html($form['site_id']).'</td>';
            echo '<td><a href="'.esc_html($form['site_url']).'/wp-admin/">'.esc_html($form['site_name']).'</a></td>';
            echo '<td>'.esc_html($form['form_id']).'</td>';
            echo '<td>'.esc_html($form['form_title']).'</td>';
            echo '<td>'.esc_html($form['form_status']).'</td>';
            echo '<td><a href="'.esc_html($form['site_url']).'/wp-admin/admin.php?page=gf_edit_forms&id='.esc_html($form['form_id']).'">View Form</a></td>';
            echo '<td>'.$form['form_links'].'</td>';
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

/**
 * @return mixed[]
 */
function search_for_specific_gravity_form($form_id): array
{
    // Define the post types to search through (in this case, only pages)
    $post_types = ['page'];

    $ids = [];

    // Set up the arguments for WP_Query
    $args = [
        'post_type' => $post_types,
        'posts_per_page' => -1, // Retrieve all posts
    ];

    // Run the query to get all pages
    $query = new WP_Query($args);

    // Check if there are any results
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Get the content of the page
            $content = get_the_content();

            $blocks = parse_blocks($content);

            foreach ($blocks as $block) {
                if ($block['blockName'] === 'gravityforms/form') {
                    if (isset($block['attrs']['formId']) && ($block['attrs']['formId'] == $form_id)) {
                        $ids[] = get_the_ID();
                    }
                }
            }
        }
    }

    // Reset post data to avoid conflicts with other queries
    wp_reset_postdata();

    return $ids;
}
