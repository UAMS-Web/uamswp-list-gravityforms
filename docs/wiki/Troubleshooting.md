Written against the unreleased `main` branch.

- [UAMSWP List All Gravity Forms needs a WordPress multisite network](#uamswp-list-all-gravity-forms-needs-a-wordpress-multisite-network)
- [Gravity Forms is not active in Network Admin](#gravity-forms-is-not-active-in-network-admin)
- [There is no List Gravity Forms menu item](#there-is-no-list-gravity-forms-menu-item)
- [Pages with Form reads None for a form that is on a page](#pages-with-form-reads-none-for-a-form-that-is-on-a-page)
- [The page is slow or times out](#the-page-is-slow-or-times-out)
- [Some sites are missing from the table](#some-sites-are-missing-from-the-table)
- [Fixed](#fixed)

## UAMSWP List All Gravity Forms needs a WordPress multisite network

The admin shows the notice `UAMSWP List All Gravity Forms needs a WordPress multisite network and does nothing on this site. Deactivate it here.`

**Cause:** the plugin is active on a WordPress install that is not a multisite network. It loads nothing there; the notice is shown to users who can activate plugins.

**Fix:** deactivate the plugin on that site, from **Plugins**.

## Gravity Forms is not active in Network Admin

Network Admin shows the notice `The List Gravity Forms page is unavailable because Gravity Forms is not active in Network Admin. Network-activate Gravity Forms to use it.`, and there is no **List Gravity Forms** item.

**Cause:** the `GFAPI` class does not exist on Network Admin requests, so the plugin adds no page. This happens when Gravity Forms is deactivated or removed, or activated on individual sites rather than network-activated. The notice is shown to network administrators.

**Fix:** network-activate Gravity Forms from **Network Admin**, **Plugins**.

## There is no List Gravity Forms menu item

The plugin is active, but Network Admin shows no **List Gravity Forms** item.

**Cause:** the item is added to the Network Admin menu only, and only for users with the `manage_network_options` capability. It is missing when Gravity Forms is not active in Network Admin (a notice then says so, see [the entry above](#gravity-forms-is-not-active-in-network-admin)), when the plugin is activated on one site rather than network-activated (WordPress does not load a per-site plugin in Network Admin), when you are looking at a site's own admin rather than Network Admin, or when your account is not a network administrator.

**Fix:** network-activate the plugin from **Network Admin**, **Plugins**, and open **Network Admin** as a network administrator.

## Pages with Form reads None for a form that is on a page

**Cause:** the column finds only a Gravity Forms block placed directly in a page's content. A form inside a group, columns or reusable block, embedded with the `[gravityform]` shortcode, on a post rather than a page, or on a trashed page is not found.

**Fix:** there is no setting to widen the search. Check the page by hand, and see [The forms table](The-forms-table.md#pages-with-form) for exactly what is searched.

## The page is slow or times out

**Cause:** every load of the page switches to each site of the network, asks Gravity Forms for its forms, and, for each form, reads every page of that site and parses its blocks. The work grows with sites, forms and pages together, and nothing is cached.

**Fix:** none in the plugin. A larger PHP time limit for the request lets it finish.

## Some sites are missing from the table

A site that has forms has no rows, and the network has more than 100 sites.

**Cause:** the plugin reads the network's sites with `get_sites()` and no arguments, which returns at most 100 sites. Sites after the first 100 are not read.

**Fix:** none in the plugin yet.

## Fixed

Entries for problems fixed after v1.1.1, kept for sites still running it.

### This script must be run in a multisite installation.

Every page of the site, the admin included, shows only this message.

**Cause:** the plugin is active on a WordPress install that is not a multisite network. Once all plugins have loaded, the plugin checks `is_multisite()` and calls `wp_die()` when it is false, on every request.

**Fix:** the admin is unreachable, so deactivate the plugin without it. Rename the plugin's folder under `wp-content/plugins/` (for example, add `-off` to its name), which WordPress treats as the plugin being removed, then delete it or move it back once the site is a network. With WP-CLI, skip the plugin while deactivating it: `wp plugin deactivate uamswp-list-gravityforms --skip-plugins=uamswp-list-gravityforms`, using the plugin's folder name if it differs.

### Gravity Forms API not available. Make sure Gravity Forms is active.

Every page of a site, the admin included, shows only this message.

**Cause:** the plugin is loaded on a request where Gravity Forms is not, so the `GFAPI` class does not exist and the plugin calls `wp_die()`. This happens when Gravity Forms is deactivated or removed, and also on any site of the network where Gravity Forms is activated per site rather than network-activated: a network-activated copy of this plugin loads on every site, including the ones without Gravity Forms.

**Fix:** network-activate Gravity Forms, so it loads on every site this plugin loads on. If the admin is already unreachable, rename this plugin's folder as in the entry above, activate Gravity Forms, then restore the folder name. With WP-CLI: `wp plugin activate gravityforms --network --skip-plugins=uamswp-list-gravityforms`, using each plugin's folder name if it differs.
