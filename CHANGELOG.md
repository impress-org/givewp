# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

<!-- Changes made since the last release are stored here until a release is ready. -->

### Fixed

-   Trailing comma in function call is removed for PHP 5.6 support. (#5195)

## [2.8.0-beta.1] - 2020-08-24

### Changed

-   Stripe Checkout modal is now rendered using Stripe Elements so that users can continue to use the modal display style even after it is deprecated by Stripe. (#4964)
-   Format for country and state select fields is normalized so states have an empty option but countries do not (#5163)
-   Scope of marked optional fields in the Multi-Step template is reduced to the User Info fieldset. (#5161)
-   Wizard buttons now match the form preview. (#5167)
-   Setup Page now initiates the connection to Stripe, but defers webhook configuration to the gateway settings. (#5171)
-   Setup Page margins are now consistent with other GiveWP admin pages. (#5180)
-   Version numbers with tags (e.g. `2.8.0-beta.1`) can now be saved in full to the database. (#5172)

### Fixed

-   Placeholder for the Base Country setting no longer reads "Select a form". (#5163)
-   Form preview within the Onboarding Wizard now remains centered on larger viewports. (#5180)
-   Fixed translation of common text to support WordPress 5.5, with backwards compatibility for `commonL10n`. (#5186)

## [2.8.0-alpha.2] - 2020-08-19

### Changed

-   Add-ons listed on the Setup Page are now denoted as suggestions based on selections made in the Wizard. (#5145)
-   Setup Page links now use short URLs that can be changed without updating the plugin. (#5146)
-   Stripe colors in the Setup Page are further differentiated from PayPal. (#5148)
-   Cause Types presented in the Wizard now include full list of options. (#5141)
-   Wizard feature for "One-Time Donations" is replaced by "Offline Donations". (#5103)
-   Wizard now prompts when exiting without completing required steps. (#5111)
-   Optional fields in the Multi-Step form template are denoted to appear distinct from required fields. (#5157)
-   Default minimum donation amount is increased from $1.00 to $5.00 to help prevent card testing spam. (#5120)

### Fixed

-   Clickable elements in the Wizard now denoted visually with a cursor pointer. (#5127)
-   Wizard now maintains a consistent width when scrolling is toggled due to changes in page height. (#5107)
-   Setup Page header logo now aligns with content container. (#5135)
-   Setup Page assets now load from the correct directory in production. (#5108)
-   Missing block links in Setup Page now added. (#5128)
-   Location settings in the Wizard now default to current setting value. (#5150)
-   Resolved style and JS issues in WordPress 5.5+ with GiveWP's WP-admin metabox expand/collapse and repeater elements. (#5126)

## [2.8.0-alpha.1] - 2020-08-17

### Added

-   New Onboarding Wizard guides new users through first-time configuration (#5014)
-   New Setup Page clarifies required steps that must be completed prior to accepting live donations (#5014)
-   New `CHANGELOG.md`, Keep a Changelog, and Semantic Versioning standards are now in place (#5117)
-   Update Stripe Checkout to use Stripe Elements (#4964)

### Changed

-   First-time installs now redirect the user to the Onboarding Wizard which can be dismissed. (#5014)

### Removed

-   Old Welcome Page has been removed in favor of the new Onboarding Wizard & Setup Page. (#5014)

### Fixed

-   The `[give_receipt]` shortcode is more compatible alongside other shortcodes, which is especially relevant for page builders. (#5044)
-   A `register_rest_route` notice no longer displays when creating a new page in the block editor. (#5115)
-   A typo in the Terms & Conditions field description has been fixed. (#5110)
-   Installed version of PHPUnit now supports PHP 5.6. (#5100)

[unreleased]: https://github.com/impress-org/givewp/compare/2.8.0-beta.1...HEAD
[2.8.0-beta.1]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.2...2.8.0-beta.1
[2.8.0-alpha.2]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.1...2.8.0-alpha.2
[2.8.0-alpha.1]: https://github.com/impress-org/givewp/releases/tag/2.8.0-alpha.1
