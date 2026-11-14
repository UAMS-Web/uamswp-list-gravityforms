<?php

/**
 * A stand-in for WordPress' WP_Query, which the Unit suite does not install.
 *
 * Only what search_for_specific_gravity_form() uses: construction with arguments,
 * and the have_posts() and the_post() loop. A test sets the posts it iterates.
 */

declare(strict_types=1);

if (! class_exists('WP_Query', false)) {
    final class WP_Query
    {
        /**
         * Each post is ['id' => int, 'content' => string].
         *
         * @var array<int, array{id: int, content: string}>
         */
        public static array $posts = [];

        /**
         * The arguments of the most recent construction.
         *
         * @var array<string, mixed>
         */
        public static array $args = [];

        /**
         * The post the_post() last advanced to.
         *
         * @var array{id: int, content: string}|null
         */
        public static ?array $current = null;

        private int $position = 0;

        /**
         * @param  array<string, mixed>  $args
         */
        public function __construct(array $args)
        {
            self::$args = $args;
        }

        public function have_posts(): bool
        {
            return $this->position < count(self::$posts);
        }

        public function the_post(): void
        {
            self::$current = self::$posts[$this->position];
            $this->position++;
        }
    }
}
