<?php namespace UAMS\GF_List;

add_action( 'plugins_loaded', 'UAMS\GF_List\bootstrap' );

/**
 * Loads the UAMSWP List All Gravity Forms
 *
 * @since 1.0.0
 */
function bootstrap() {
    // include_once (__DIR__ . '/class.gf-block-finder.php' );
    include_once (__DIR__ . '/class.gf-list-functions.php' );
}