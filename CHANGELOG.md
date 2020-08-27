# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

<!-- Changes made since the last release are stored here until a release is ready. -->

### Changed

-   Update Onboarding copy text and typos. (#5168)
-   Reduced padding on the Setup Page container. (#5165)
-   Normalizes format for country and state selects, with states having an empty option and countries not. (#5163)
-   Reduced the scope of marked optional fields in the Multi-Step template to the User Info fieldset. (#5161)
-   Added hover styles to the Wizard buttons to match the form preview. (#5167)
-   Setup Page now initiates the connection to Stripe, but defers webhook configuration to the gateway settings. (#5171)
-   Removed preg_match that prevented version numbers with tags from being stored (#5172)
-   Admin notice animation has been removed. (#5182)
-   Multi-step forms now support RTL styles (#5196)

### Fixed

-   Set a specific placeholder for the base country select setting, no longer reads "Select a form". (#5163)
-   Form preview within the Onboarding Wizard now remains centered on larger viewports. (#5180)
-   Onboarding Wizard no longer shows empty submenu under Dashboard. (#5190)
-   Multi-step form anonymous donation checkbox is now checkable after changing the payment gateway (#5191)


## [2.8.0-alpha.2] - 2020-08-19

### Changed

-   Note on test mode added to instructions for configuring Stripe Webhooks in the Setup Page. (#5149)
-   Add-ons listed on the Setup Page now denoted as suggestions based on selections made in the Wizard. (#5145)
-   Setup Page links now using short URLs that can be changed without updating the plugin. (#5146)
-   Stripe colors in the Setup Page now further differentiate from PayPal. (#5148)
-   Cause Types presented in the Wizard now include full list of options. (#5141)
-   Stripe account in Setup Page now communicates connected state as completed. (#5132)
-   Stripe button to configure webhooks now communicates polling state while waiting for configuration. (#5131)
-   Wizard feature "One-Time Donations" replaced with "Offline Donations". (#5103)
-   Wizard now prompts when exiting without completing required steps. (#5111)
-   Plugin links updated with short URLs. (#5156)
-   Denotes optional fields in the Multi-Step form template to differentiate from required. (#5157)
-   Increased the minimum donation amount default value from $1.00 to $5.00 to help prevent card testing (#5120)

### Fixed

-   Clickable elements in the Wizard now denoted visually with a cursor pointer. (#5127)
-   Wizard now maintains a consistent width when scrolling is toggled be changes in page height. (#5107)
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

[unreleased]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.2...HEAD
[2.8.0-alpha.2]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.1...2.8.0-alpha.2
[2.8.0-alpha.1]: https://github.com/impress-org/givewp/releases/tag/2.8.0-alpha.1
