# Gravity Forms List Plugin for WordPress Multisite

## Overview
This plugin lists all Gravity Forms across a WordPress multisite network. It adds a **List Gravity Forms** page to Network Admin with a table of every form on every site: its site, ID, title and status, a link to edit it, and the pages that embed it.

The documentation is in the [wiki](https://github.com/UAMS-Web/uamswp-list-gravityforms/wiki): a walkthrough, the reference for the forms table, troubleshooting, and how the plugin works.

## Requirements
- **WordPress Multisite** 6.8 or later. On a single-site install the plugin stops every request ([Troubleshooting](https://github.com/UAMS-Web/uamswp-list-gravityforms/wiki/Troubleshooting)).
- **PHP** 8.2 or later.
- **Gravity Forms**, network-activated, so that it loads on every site this plugin loads on.

## Installation

1. Put the plugin in `wp-content/plugins/uamswp-list-gravityforms/`, either by downloading this repository or by cloning it there.
2. In **Network Admin**, **Plugins**, network-activate *UAMSWP List All Gravity Forms*.
3. Open **Network Admin**, **List Gravity Forms**. The [walkthrough](https://github.com/UAMS-Web/uamswp-list-gravityforms/wiki/Listing-every-form-on-the-network) goes through the page.

## Credits

Developed and maintained by UAMS Web Services.

Developers:

- Josh Daugherty
- Todd McKee

## Contributing
Contributions are welcome! If you have any improvements or bug fixes, feel free to fork this repository, make your changes, and submit a pull request.

## License
This plugin is provided under the [GNU General Public License v2 (or later)](https://www.gnu.org/licenses/gpl-2.0.html).


## Code quality

`composer test:pint` checks formatting (Pint, `laravel` preset), `composer test:refactor` previews Rector's rules and `composer stan` runs PHPStan at level 10. `composer pint` and `composer refactor` apply their fixes. The Pint reformat commit is listed in `.git-blame-ignore-revs`; run `git config blame.ignoreRevsFile .git-blame-ignore-revs` once per clone so `git blame` skips it.

## Tests

The unit suite runs on Pest 5 with Brain Monkey, so it needs no WordPress install and no database. It develops and tests on PHP 8.4.27 (`config.platform.php` in `composer.json`); the declared plugin floor is unchanged and is tracked separately.

```bash
composer install
composer test
```

`composer test` runs `vendor/bin/pest`. Remote CI runs the same suite alongside its other jobs on every pull request, and it is the gate; there is no local runner.

Test files are named `*Test.php` under `tests/Unit/`; a file named otherwise never runs.
