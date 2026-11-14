# Gravity Forms List Plugin for WordPress Multisite

## Overview
This PHP script is a custom plugin designed to list all Gravity Forms across a WordPress multisite installation. It integrates with the Gravity Forms API to retrieve forms from each site within the network and displays them in a table format on a dedicated admin page under Network Admin.

## Requirements
- **WordPress Multisite**: This script must be run in a WordPress multisite environment.
- **Gravity Forms Plugin**: Ensure that the Gravity Forms plugin is installed and activated on your network.

## Features
- Lists all forms from each site within the WordPress multisite network.
- Displays form details such as Site ID, Site Name, Form ID, Form Title, and Form Status.
- Provides a direct link to view the form in the respective site's admin area.

## Installation

### Step-by-Step Instructions:
1. **Create a Plugin File:**
   - Navigate to your WordPress installation directory.
   - Go to `wp-content/plugins/`.
   - Create a new folder named, for example, `gravity-forms-list` or any name you prefer.
   - Inside this folder, create a PHP file, e.g., `index.php`.

2. **Add the Code:**
   - Copy the provided code into your newly created PHP file (`index.php`).

3. **Activate the Plugin:**
   - Go to the WordPress admin dashboard of any site in the network.
   - Navigate to Network Admin > Plugins (or use the URL `yourdomain.com/wp-admin/network/plugins.php`).
   - Find "Gravity Forms List" (or whatever name you used) and activate it.

## Usage
- After activating the plugin, a new menu item named "List Gravity Forms" will appear under Network Admin.
- Click on this menu to view a list of all Gravity Forms across the multisite network.
- The table includes columns for Site ID, Site Name, Form ID, Form Title, and Form Status. Each row also contains a link to view the form in its respective site’s admin area.

## Code Explanation
1. **Multisite Check:**
   - Ensures that the script runs only in a multisite installation.
   
2. **Gravity Forms API Check:**
   - Checks if the Gravity Forms API is available by verifying if the `GFAPI` class exists.
   
3. **Function `get_gravity_forms_for_site`:**
   - Switches to a specific blog/site using `switch_to_blog`.
   - Retrieves all forms from that site using `GFAPI::get_forms()`.
   - Collects details such as Site ID, Site Name, Form ID, Form Title, and Form Status.
   - Restores the current blog after retrieving data.

4. **Menu Page Creation:**
   - Adds a new menu page under Network Admin using `add_menu_page`.
   
5. **Admin Page Display:**
   - Retrieves all forms from each site in the network by iterating through sites using `get_sites()`.
   - Displays form details in a table format.
   - Provides direct links to view forms in their respective admin areas.

## Notes
- This script is intended for use in a WordPress multisite environment where Gravity Forms is installed and activated.
- The commented-out section (related to `Gravity_Forms_Block_Finder`) could be used to find pages that include specific forms, but it requires additional setup and might not work with all versions of Gravity Forms.

## Credits

Developed and maintained by UAMS Web Services.

Developers:

- Josh Daugherty

## Contributing
Contributions are welcome! If you have any improvements or bug fixes, feel free to fork this repository, make your changes, and submit a pull request.

## License
This plugin is provided under the [GNU General Public License v2 (or later)](https://www.gnu.org/licenses/gpl-2.0.html).


## Code quality

`composer test:pint` checks formatting (Pint, `laravel` preset), `composer test:refactor` previews Rector's rules and `composer stan` runs PHPStan at level 0. `composer pint` and `composer refactor` apply their fixes. The Pint reformat commit is listed in `.git-blame-ignore-revs`; run `git config blame.ignoreRevsFile .git-blame-ignore-revs` once per clone so `git blame` skips it.

## Tests

The unit suite runs on Pest 5 with Brain Monkey, so it needs no WordPress install and no database. It develops and tests on PHP 8.4.27 (`config.platform.php` in `composer.json`); the declared plugin floor is unchanged and is tracked separately.

```bash
composer install
composer test
```

`composer test` runs `vendor/bin/pest`. The shared local CI runner runs the same suite alongside its other jobs:

```bash
node .claude/skills/local-ci/local-ci.mjs
```

Test files are named `*Test.php` under `tests/Unit/`; a file named otherwise never runs.
