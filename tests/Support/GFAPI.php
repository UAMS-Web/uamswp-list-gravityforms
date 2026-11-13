<?php

/**
 * A stand-in for Gravity Forms' GFAPI class, which the Unit suite does not install.
 *
 * Only the method the plugin calls. Tests set the forms it returns.
 */

declare(strict_types=1);

if (! class_exists('GFAPI', false)) {
    final class GFAPI
    {
        /**
         * @var array<int, array<string, mixed>>
         */
        public static array $forms = [];

        /**
         * @return array<int, array<string, mixed>>
         */
        public static function get_forms(): array
        {
            return self::$forms;
        }
    }
}
