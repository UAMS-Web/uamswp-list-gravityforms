<?php

/**
 * search_for_specific_gravity_form(), with WP_Query and the post template tags stood in.
 *
 * Each post's content is a JSON list of blocks, which parse_blocks() is mocked to decode.
 *
 * @see includes/class.gf-list-functions.php
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\expect as expectCall;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    require_once __DIR__.'/../Support/load-plugin.php';

    gf_list_tests_load_plugin();

    WP_Query::$posts = [];
    WP_Query::$args = [];
    WP_Query::$current = null;

    when('get_the_content')->alias(fn (): string => WP_Query::$current['content'] ?? '');
    when('get_the_ID')->alias(fn (): int => WP_Query::$current['id'] ?? 0);
});

/**
 * Stub the template tags a test does not set expectations on.
 *
 * Brain Monkey replaces a stub with an expectation only when the stub is not defined
 * first, so each test names what it will expect on and the rest are stubbed here.
 *
 * @param  array<int, string>  $except
 */
function gf_list_tests_stub_search(array $except = []): void
{
    if (! in_array('parse_blocks', $except, true)) {
        when('parse_blocks')->alias(fn (string $content): array => json_decode($content, true));
    }

    if (! in_array('wp_reset_postdata', $except, true)) {
        when('wp_reset_postdata')->justReturn();
    }
}

/**
 * @param  array<int, array<string, mixed>>  $blocks
 * @return array{id: int, content: string}
 */
function gf_list_tests_post(int $id, array $blocks): array
{
    return ['id' => $id, 'content' => json_encode($blocks, JSON_THROW_ON_ERROR)];
}

/**
 * @return array<string, mixed>
 */
function gf_list_tests_form_block(mixed $form_id): array
{
    return ['blockName' => 'gravityforms/form', 'attrs' => ['formId' => $form_id]];
}

it('searches every page, without paging', function (): void {
    gf_list_tests_stub_search();
    search_for_specific_gravity_form(3);

    expect(WP_Query::$args)->toBe(['post_type' => ['page'], 'posts_per_page' => -1]);
});

it('finds nothing when there are no pages', function (): void {
    gf_list_tests_stub_search(['wp_reset_postdata']);
    expectCall('wp_reset_postdata')->once();

    expect(search_for_specific_gravity_form(3))->toBe([]);
});

it('returns the id of a page that holds the form block', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [gf_list_tests_post(41, [gf_list_tests_form_block(3)])];

    expect(search_for_specific_gravity_form(3))->toBe([41]);
});

it('matches a form id that is a string against an integer', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [gf_list_tests_post(41, [gf_list_tests_form_block('3')])];

    expect(search_for_specific_gravity_form(3))->toBe([41]);
});

it('skips a block for a different form', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [gf_list_tests_post(41, [gf_list_tests_form_block(4)])];

    expect(search_for_specific_gravity_form(3))->toBe([]);
});

it('skips a form block that has no form id', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [gf_list_tests_post(41, [['blockName' => 'gravityforms/form', 'attrs' => []]])];

    expect(search_for_specific_gravity_form(3))->toBe([]);
});

it('skips a block that is not a Gravity Forms form block', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [gf_list_tests_post(41, [['blockName' => 'core/paragraph', 'attrs' => ['formId' => 3]]])];

    expect(search_for_specific_gravity_form(3))->toBe([]);
});

it('finds the form across several pages and blocks, in page order', function (): void {
    gf_list_tests_stub_search();
    WP_Query::$posts = [
        gf_list_tests_post(10, [['blockName' => 'core/paragraph', 'attrs' => []], gf_list_tests_form_block(3)]),
        gf_list_tests_post(11, [gf_list_tests_form_block(9)]),
        gf_list_tests_post(12, [gf_list_tests_form_block(3), gf_list_tests_form_block(3)]),
    ];

    expect(search_for_specific_gravity_form(3))->toBe([10, 12, 12]);
});

it('parses the content of each page', function (): void {
    gf_list_tests_stub_search(['parse_blocks']);
    WP_Query::$posts = [gf_list_tests_post(41, []), gf_list_tests_post(42, [])];

    expectCall('parse_blocks')->twice()->andReturn([]);

    search_for_specific_gravity_form(3);
});

it('resets the post data once after the loop', function (): void {
    gf_list_tests_stub_search(['wp_reset_postdata']);
    WP_Query::$posts = [gf_list_tests_post(41, []), gf_list_tests_post(42, [])];

    expectCall('wp_reset_postdata')->once();

    search_for_specific_gravity_form(3);
});
