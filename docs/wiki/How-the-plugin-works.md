Written against the unreleased `main` branch.

What the code does on each request, and where the plugin is developed.

## On each request

1. **The main file** (`uamswp-list-gravityforms.php`) stops if it is loaded outside WordPress, defines the plugin's path, URL and version constants, and includes `includes/gf-list.php`.
2. **On `plugins_loaded`**, `UAMS\GF_List\bootstrap()` checks the environment. On a single-site install it hooks `UAMS\GF_List\notice_not_multisite()` to `admin_notices` and stops there. On a network where the `GFAPI` class does not exist, it hooks `UAMS\GF_List\notice_no_gravity_forms()` to `network_admin_notices` and stops there. Neither case stops the request (see [Troubleshooting](Troubleshooting.md)).
3. **Otherwise it includes `includes/class.gf-list-functions.php`**, which hooks `gf_list_admin_menu()` to `network_admin_menu` to add the **List Gravity Forms** page.

## When the page is opened

1. `gf_list_admin_page()` reads every site of the network from `get_sites()`, passing `number` `0` to lift its default limit of 100.
2. For each site, `get_gravity_forms_for_site()` switches to it with `switch_to_blog()`, reads its forms with `GFAPI::get_forms()`, and reads its name and URL.
3. For each form, `search_for_specific_gravity_form()` queries the site's pages, parses each page's blocks with `parse_blocks()`, and collects the pages with a top-level `gravityforms/form` block for that form.
4. The site is restored with `restore_current_blog()`, and the rows are printed as one HTML table. Each cell value passes through `esc_html()`, except the **Pages with Form** cell, which is printed as built: its links carry each page's title through `esc_html()` and its edit URL as `get_edit_post_link()` returns it.

The commented-out include of `class.gf-block-finder.php` in `includes/gf-list.php` refers to a file that is not in the repository.

## Development

- **Repository:** [UAMS-Web/uamswp-list-gravityforms](https://github.com/UAMS-Web/uamswp-list-gravityforms). Its [README](https://github.com/UAMS-Web/uamswp-list-gravityforms#readme) covers the code-quality tools and the test suite.
- **Contributing:** [CONTRIBUTING.md](https://github.com/UAMS-Web/uamswp-list-gravityforms/blob/main/CONTRIBUTING.md) sets out the issue-to-merge path.
- [Report a problem](https://github.com/UAMS-Web/uamswp-list-gravityforms/issues)
- [Releases](https://github.com/UAMS-Web/uamswp-list-gravityforms/releases)
