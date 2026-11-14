<?php

/**
 * get_gravity_forms_for_site(), with WordPress, Gravity Forms and WP_Query stood in.
 *
 * @see includes/class.gf-list-functions.php
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\expect as expectCall;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    require_once __DIR__.'/../Support/load-plugin.php';

    gf_list_tests_load_plugin();

    GFAPI::$forms = [];
    WP_Query::$posts = [];
    WP_Query::$current = null;

    when('get_bloginfo')->alias(fn (string $show): string => match ($show) {
        'name' => 'Site Name',
        'url' => 'https://site.test',
        default => '',
    });
    when('get_the_content')->alias(fn (): string => WP_Query::$current['content'] ?? '');
    when('get_the_ID')->alias(fn (): int => WP_Query::$current['id'] ?? 0);
    when('parse_blocks')->alias('gf_list_tests_parse_blocks');
    when('wp_reset_postdata')->justReturn();
    when('get_edit_post_link')->alias(fn (int $id): string => "https://site.test/wp-admin/post.php?post={$id}");
    when('get_the_title')->alias(fn (int $id): string => "Page {$id}");
    when('esc_html')->alias(fn (string $text): string => "[{$text}]");
});

it('switches to the site, and back, around the Gravity Forms lookup', function (): void {
    expectCall('switch_to_blog')->once()->with(7);
    expectCall('restore_current_blog')->once();

    expect(get_gravity_forms_for_site(7))->toBe([]);
});

it('describes each form with the site it belongs to', function (): void {
    when('switch_to_blog')->justReturn(true);
    when('restore_current_blog')->justReturn(true);

    GFAPI::$forms = [
        ['id' => 1, 'title' => 'Contact', 'is_active' => true],
        ['id' => 2, 'title' => 'Survey', 'is_active' => false],
    ];

    expect(get_gravity_forms_for_site(7))->toBe([
        [
            'site_id' => 7,
            'site_name' => 'Site Name',
            'site_url' => 'https://site.test',
            'form_id' => 1,
            'form_title' => 'Contact',
            'form_status' => 'Active',
            'form_links' => 'None',
        ],
        [
            'site_id' => 7,
            'site_name' => 'Site Name',
            'site_url' => 'https://site.test',
            'form_id' => 2,
            'form_title' => 'Survey',
            'form_status' => 'Inactive',
            'form_links' => 'None',
        ],
    ]);
});

it('links every page that holds the form, with the title escaped', function (): void {
    when('switch_to_blog')->justReturn(true);
    when('restore_current_blog')->justReturn(true);

    GFAPI::$forms = [['id' => 1, 'title' => 'Contact', 'is_active' => true]];
    $block = json_encode([['blockName' => 'gravityforms/form', 'attrs' => ['formId' => 1]]], JSON_THROW_ON_ERROR);
    WP_Query::$posts = [['id' => 10, 'content' => $block], ['id' => 11, 'content' => $block]];

    expect(get_gravity_forms_for_site(7)[0]['form_links'])->toBe(
        '<a href="https://site.test/wp-admin/post.php?post=10">[Page 10]</a><br>'
        .'<a href="https://site.test/wp-admin/post.php?post=11">[Page 11]</a><br>'
    );
});

it('searches the pages for each form by its id', function (): void {
    when('switch_to_blog')->justReturn(true);
    when('restore_current_blog')->justReturn(true);

    GFAPI::$forms = [
        ['id' => 1, 'title' => 'Contact', 'is_active' => true],
        ['id' => 2, 'title' => 'Survey', 'is_active' => true],
    ];
    WP_Query::$posts = [[
        'id' => 10,
        'content' => json_encode([['blockName' => 'gravityforms/form', 'attrs' => ['formId' => 2]]], JSON_THROW_ON_ERROR),
    ]];

    $forms = get_gravity_forms_for_site(7);

    expect($forms[0]['form_links'])->toBe('None')
        ->and($forms[1]['form_links'])->toContain('post=10');
});
