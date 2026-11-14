<?php

/**
 * gf_list_admin_page(), with WordPress, Gravity Forms and WP_Query stood in.
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

    when('switch_to_blog')->justReturn(true);
    when('restore_current_blog')->justReturn(true);
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
    // Like the real function, which takes a string: an int or a null would be a defect in the caller.
    when('esc_html')->alias(fn (mixed $text): string => is_string($text)
        ? "[{$text}]"
        : throw new TypeError('esc_html() takes a string, '.get_debug_type($text).' given.'));
});

/**
 * @param  array<int, int>  $blog_ids
 * @return array<int, object>
 */
function gf_list_tests_sites(array $blog_ids): array
{
    return array_map(fn (int $blog_id): object => (object) ['blog_id' => $blog_id], $blog_ids);
}

function gf_list_tests_render(): string
{
    ob_start();
    gf_list_admin_page();

    return (string) ob_get_clean();
}

it('says so when no site has a form', function (): void {
    when('get_sites')->justReturn(gf_list_tests_sites([1]));

    $html = gf_list_tests_render();

    expect($html)->toContain('<div class="wrap">')
        ->and($html)->toContain('<h1>All Gravity Forms on Network</h1>')
        ->and($html)->toContain('No forms found in network sites.')
        ->and($html)->not->toContain('<table');
});

it('says so when the network has no sites', function (): void {
    when('get_sites')->justReturn([]);

    expect(gf_list_tests_render())->toContain('No forms found in network sites.');
});

it('lists the forms of every site in one table', function (): void {
    expectCall('get_sites')->once()->andReturn(gf_list_tests_sites([1, 5]));
    GFAPI::$forms = [['id' => 3, 'title' => 'Contact', 'is_active' => true]];

    $row = static fn (int $site_id): string => '<tr>'
        ."<td>[{$site_id}]</td>"
        .'<td><a href="[https://site.test]/wp-admin/">[Site Name]</a></td>'
        .'<td>[3]</td>'
        .'<td>[Contact]</td>'
        .'<td>[Active]</td>'
        .'<td><a href="[https://site.test]/wp-admin/admin.php?page=gf_edit_forms&id=[3]">View Form</a></td>'
        .'<td>None</td>'
        .'</tr>';

    expect(gf_list_tests_render())->toContain(
        '<table border="1" cellpadding="5" cellspacing="0">'
        .'<tr><th>Site ID</th><th>Site Name</th><th>Form ID</th><th>Form Name</th><th>Form Status</th><th>&nbsp;</th><th>Pages with Form</th></tr>'
        .$row(1).$row(5)
        .'</table>'
    );
});

it('prints the page links of a form unescaped, since they are already markup', function (): void {
    when('get_sites')->justReturn(gf_list_tests_sites([1]));
    GFAPI::$forms = [['id' => 3, 'title' => 'Contact', 'is_active' => false]];
    WP_Query::$posts = [[
        'id' => 10,
        'content' => json_encode([['blockName' => 'gravityforms/form', 'attrs' => ['formId' => 3]]], JSON_THROW_ON_ERROR),
    ]];

    $html = gf_list_tests_render();

    expect($html)->toContain('<td>[Inactive]</td>')
        ->and($html)->toContain('<td><a href="https://site.test/wp-admin/post.php?post=10">[Page 10]</a><br></td>');
});
