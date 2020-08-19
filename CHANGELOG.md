# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

<!-- Changes made since the last release are stored here until a release is ready. -->

## [2.8.0-alpha.1] - 2020-08-17

### Added

-   New Onboarding Wizard guides new users through first-time configuration (#5014)
-   New Setup Page clarifies required steps that must be completed prior to accepting live donations (#5014)
-   New `CHANGELOG.md`, Keep a Changelog, and Semantic Versioning standards are now in place (#5117)

### Changed

-   First-time installs now redirect the user to the Onboarding Wizard which can be dismissed (#5014)

### Removed

-   Old Welcome Page has been removed in favor of the new Onboarding Wizard & Setup Page (#5014)

### Fixed

-   The `[give_receipt]` shortcode is more compatible alongside other shortcodes, which is especially relevant for page builders (#5044)
-   A `register_rest_route` notice no longer displays when creating a new page in the block editor (#5115)
-   A typo in the Terms & Conditions field description has been fixed (#5110)
-   Installed version of PHPUnit now supports PHP 5.6 (#5100)
-   Resolved style and JS issues in WordPress 5.5+ with GiveWP's WP-admin metabox expand / collapse, and repeater elements. (#5118)

[unreleased]: https://github.com/impress-org/givewp/compare/2.8.0-alpha.1...HEAD
[2.8.0-alpha.1]: https://github.com/impress-org/givewp/releases/tag/2.8.0-alpha.1
