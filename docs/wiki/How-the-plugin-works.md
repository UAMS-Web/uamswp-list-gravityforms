Written against v1.1.1.

What the code does on each request, and where the plugin is developed.

## On each request

1. **The main file** (`uamswp-list-gravityforms.php`) stops if it is loaded outside WordPress, defines the plugin's path, URL and version constants, and includes `includes/gf-list.php`.
2. **On `plugins_loaded`**, `UAMS\GF_List\bootstrap()` includes `includes/class.gf-list-functions.php`.
3. **That file checks its environment first**: it calls `wp_die()` unless the install is a multisite network and the `GFAPI` class exists (see [Troubleshooting](Troubleshooting.md)). It then hooks `gf_list_admin_menu()` to `network_admin_menu`, which adds the **List Gravity Forms** page.

## When the page is opened

1. `gf_list_admin_page()` reads the sites from `get_sites()` with no arguments, which returns at most 100.
2. For each site, `get_gravity_forms_for_site()` switches to it with `switch_to_blog()`, reads its forms with `GFAPI::get_forms()`, and reads its name and URL.
3. For each form, `search_for_specific_gravity_form()` queries the site's pages, parses each page's blocks with `parse_blocks()`, and collects the pages with a top-level `gravityforms/form` block for that form.
4. The site is restored with `restore_current_blog()`, and the rows are printed as one HTML table. Each cell value passes through `esc_html()`, except the **Pages with Form** cell, which is printed as built: its links carry each page's title through `esc_html()` and its edit URL as `get_edit_post_link()` returns it.

The commented-out include of `class.gf-block-finder.php` in `includes/gf-list.php` refers to a file that is not in the repository.

## Development

- **Repository:** [UAMS-Web/uamswp-list-gravityforms](https://github.com/UAMS-Web/uamswp-list-gravityforms). Its [README](https://github.com/UAMS-Web/uamswp-list-gravityforms#readme) covers the code-quality tools and the test suite.
- **Contributing:** [CONTRIBUTING.md](https://github.com/UAMS-Web/uamswp-list-gravityforms/blob/main/CONTRIBUTING.md) sets out the issue-to-merge path.
- [Report a problem](https://github.com/UAMS-Web/uamswp-list-gravityforms/issues)
- [Releases](https://github.com/UAMS-Web/uamswp-list-gravityforms/releases)
