Written against the unreleased `main` branch.

At the end of this walkthrough you will have the forms table open in Network Admin, listing every Gravity Forms form on every site in the network.

**Note:** the plugin needs a multisite network with Gravity Forms network-activated. Without either, it adds no page and shows an admin notice saying which is missing; see [Troubleshooting](Troubleshooting.md#gravity-forms-is-not-active-in-network-admin).

1. **Install the plugin** as the [Installation section of the README](https://github.com/UAMS-Web/uamswp-list-gravityforms#installation) describes, and network-activate it from **Network Admin**, **Plugins**. The plugin appears there as *UAMSWP List All Gravity Forms*.
2. **Open the page.** In Network Admin, choose **List Gravity Forms** in the left-hand menu (the forms icon). The page is titled *All Gravity Forms on Network*. Its address is `https://example.com/wp-admin/network/admin.php?page=gf-list`, with your network's domain in place of `example.com`.
3. **Read the table.** Each row is one form on one site, grouped by site in the order WordPress lists the sites. The columns are described in [The forms table](The-forms-table.md). On a network of more than 100 sites, only the first 100 are listed; see [Troubleshooting](Troubleshooting.md#some-sites-are-missing-from-the-table).
4. **Open a form.** Choose **View Form** on a row to open that form in the Gravity Forms editor of the site it belongs to.

**Note:** the page builds the whole table on every load, switching to each site in turn and reading every page on it. On a large network it can take a while to appear.

## Next

- [Finding the pages that embed a form](Finding-the-pages-that-embed-a-form.md)
- [The forms table](The-forms-table.md)
