# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

-   Totals block is now available in the block editor (#5264)
-   Totals block now supports custom goal color (#5267)
-   Migrations framework for database migrations
-   Multi-Form Goal block and shortcode are now available (#5307)
-   Progress Bar block now features improved SSR previews (#5316)
-   Multi-Form Goal block now supports "wide" alignment (#5315)
-   Multi-Form Goal block now supports the theme's color palette (#5319)
-   Multi-Form Goal block and shortcode appearance is now consistent (#5320)

### Changed

-   Multi-Form Goal wrapper only added for non-block output (#5315)
-   Multi-Form Goal output has a bottom margin (#5333)
-   Multi-Form Goal end date now allows for specific time (#5336)
-   Progress Bar block is no longer available outside of Multi-form Goal (#5338)

### Changed

-   Multi-Form Goal Block now defaults to image filling the height (#5314)

## [2.8.0] - 2020-08-31

## [2.8.0-rc.1] - 2020-08-31

### Fixed

-   Resolved a conflict with the User Avatar plugin due to improper HTML output of the user profile field markup. (#5218)
-   PHP Notices no longer break multi-step form receipt step. (#5219)
-   Fee Recovery checkbox placement in Multi-Step forms now respects the Fee Recovery input location setting. (#5205)
-   Form Field Manager fields are now set up on init of the Multi-Step form to ensure they work with only a single gateway enabled. (#5216)

### Added

-   Introduced Currency Switcher compatability styles for the Multi-Step form (#5220)

## [2.8.0-beta.3] - 2020-08-27

### Added

-   Multi-step forms now support RTL styles. (#5196)

### Fixed

-   Deprecated jQuery warnings no longer appear when jQuery Migrate Helper plugin is active. (#5184)
-   Multi-step form anonymous donation checkbox is now checkable after changing the payment gateway. (#5191)

### Changed

-   Onboarding Form Preview default image has been updated. (#5203)
-   Stripe Checkout modal max-width has been increased to fit-content. (#5209)
-   If the Setup Page is disabled, Onboarding Wizard now directs users to the All Forms page. (#5211)
-   On a fresh install, the donation forms archive is now enabled by default. (#5214)
-   Specify Form Route URL scheme to avoid mixed content when loaded in the admin. (#5189)

## [2.8.0-beta.2] - 2020-08-25

### Fixed

-   Trailing comma in function call is removed for PHP 5.6 support. (#5195)
-   Fixed translation of common text to support WordPress 5.5, with backwards compatibility for `commonL10n`. (#5186)

## [2.8.0-beta.1] - 2020-08-24

### Changed

-   Stripe Checkout modal is now rendered using Stripe Elements so that users can continue to use the modal display style even after it is deprecated by Stripe. (#4964)
-   Format for country and state select fields is normalized so states have an empty option but countries do not. (#5163)
-   Scope of marked optional fields in the Multi-Step template is reduced to the User Info fieldset. (#5161)
-   Wizard buttons now match the form preview. (#5167)
-   Setup Page now initiates the connection to Stripe, but defers webhook configuration to the gateway settings. (#5171)
-   Removed preg_match that prevented version numbers with tags from being stored (#5172)
-   Admin notice animation has been removed. (#5182)
-   Setup Page margins are now consistent with other GiveWP admin pages. (#5180)
-   Version numbers with tags (e.g. `2.8.0-beta.1`) can now be saved in full to the database. (#5172)

### Fixed

-   Placeholder for the Base Country setting no longer reads "Select a form". (#5163)
-   Form preview within the Onboarding Wizard now remains centered on larger viewports. (#5180)
-   Onboarding Wizard no longer shows empty submenu under Dashboard. (#5190)

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

-   New Onboarding Wizard guides new users through first-time configuration. (#5014)
-   New Setup Page clarifies required steps that must be completed prior to accepting live donations. (#5014)
-   New `CHANGELOG.md`, Keep a Changelog, and Semantic Versioning standards are now in place. (#5117)
-   Update Stripe Checkout to use Stripe Elements. (#4964)

### Changed

-   First-time installs now redirect the user to the Onboarding Wizard which can be dismissed. (#5014)

### Removed

-   Old Welcome Page has been removed in favor of the new Onboarding Wizard & Setup Page. (#5014)

### Fixed

-   The `[give_receipt]` shortcode is more compatible alongside other shortcodes, which is especially relevant for page builders. (#5044)
-   A `register_rest_route` notice no longer displays when creating a new page in the block editor. (#5115)
-   A typo in the Terms & Conditions field description has been fixed. (#5110)
-   Installed version of PHPUnit now supports PHP 5.6. (#5100)

[unreleased]: https://github.com/impress-org/givewp/compare/2.8.0...HEAD
[2.8.0]: https://github.com/impress-org/givewp/compare/2.8.0-rc.1...2.8.0
[2.8.0-rc.1]: https://github.com/impress-org/givewp/compare/2.8.0-beta.3...2.8.0-rc.1
[2.8.0-beta.3]: https://github.com/impress-org/givewp/compare/2.8.0-beta.2...2.8.0-beta.3
[2.8.0-beta.2]: https://github.com/impress-org/givewp/compare/2.8.0-beta.1...2.8.0-beta.2
[2.8.0-beta.1]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.2...2.8.0-beta.1
[2.8.0-alpha.2]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.1...2.8.0-alpha.2
[2.8.0-alpha.1]: https://github.com/impress-org/givewp/releases/tag/2.8.0-alpha.1
