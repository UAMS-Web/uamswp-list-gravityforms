Written against v1.1.1.

The forms table is the one page the plugin adds. It lists every form that Gravity Forms reports on each site of the network.

## The menu item

| Setting | Value |
| --- | --- |
| Where | Network Admin, top-level menu |
| Menu label | List Gravity Forms |
| Page title | All Gravity Forms on Network |
| Page slug | `gf-list` (`/wp-admin/network/admin.php?page=gf-list`) |
| Capability required | `manage_network_options` (network administrators) |
| Icon | `dashicons-forms` |

The plugin adds nothing to any site's own admin menu, and has no settings.

## Rows

There is one row per form per site. The plugin reads the sites WordPress returns from `get_sites()` with no arguments, switches to each in turn, and asks Gravity Forms for that site's forms with `GFAPI::get_forms()`, also with no arguments, so which forms appear (for example, whether inactive or trashed forms are included) is whatever Gravity Forms returns by default. When no site has a form, the page reads `No forms found in network sites.`

**Warning:** `get_sites()` with no arguments returns at most 100 sites (its default `number`). On a network with more than 100 sites, the table lists the forms of the first 100 only, and says nothing about the rest.

## Columns

| Column | Shows | Links to |
| --- | --- | --- |
| Site ID | The site's blog ID | Nothing |
| Site Name | The site's title (`get_bloginfo('name')`) | That site's dashboard, `<site URL>/wp-admin/` |
| Form ID | The form's ID on that site | Nothing |
| Form Name | The form's title | Nothing |
| Form Status | `Active` or `Inactive`, from the form's active flag | Nothing |
| (no heading) | View Form | The Gravity Forms editor for that form, `<site URL>/wp-admin/admin.php?page=gf_edit_forms&id=<form ID>` |
| Pages with Form | The pages that embed the form, or `None` | Each page's editor |

Form IDs are per site: form `1` on one site and form `1` on another are different forms.

### Pages with Form

For each form, the plugin reads the pages of the form's site and lists each one whose content holds a Gravity Forms block (`gravityforms/form`) whose form is that form. A page is listed once for each matching block.

What it reads:

- **Pages only.** Posts and other post types are not searched.
- **The pages a default `WP_Query` returns in the admin** for post type `page` with no limit: published, draft, pending and scheduled pages, and private ones the current user can read. Trashed pages are not searched.
- **Top-level blocks only.** A Gravity Forms block inside another block, such as a group, columns or a reusable block, is not found.
- **Blocks only.** A form embedded with the `[gravityform]` shortcode, through a widget, or from a theme template is not found.

So `None` means no page matched on those terms, not that the form is unused.

## Example

A network with two sites, where site 2 has one form embedded on its *Contact* page:

| Site ID | Site Name | Form ID | Form Name | Form Status | | Pages with Form |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Example Network | 1 | Newsletter sign-up | Active | View Form | None |
| 2 | Example Department | 1 | Contact us | Active | View Form | Contact |
