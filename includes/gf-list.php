<?php

declare(strict_types=1);

namespace UAMS\GF_List;

add_action('plugins_loaded', 'UAMS\GF_List\bootstrap');

/**
 * Loads the UAMSWP List All Gravity Forms
 *
 * @since 1.0.0
 */
function bootstrap(): void
{
    // include_once (__DIR__ . '/class.gf-block-finder.php' );
    // Scratch: touch this class so the mutate job selects it.
    include_once __DIR__.'/class.gf-list-functions.php';
}
