# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Added
-   Front-end donor profiles are scaffolded (#5441)
-   Donor Profiles UI is implemented (#5444)
-   Donation History and Dashboard tabs are now dynamically populated (#5455)
-   Edit Profile UI is now implemented (#5463)

## 2.9.6 - 2021-01-13

### New
-   Update warning message for unsuccessful PayPal account onboarding (#5522) 
-   Add filter hook to filter PayPal settings (#5502)
-   Sample onboarding tests are now implemented (#5543)
-   Onboarding wizard e2e tests are now implemented (#5550)
-   Setup page is e2e tested (#5547)

### Changed

-   Automated unit and integrations tests are now using GitHub actions, instead of Travis CI (#5489)
-   Resolve Avatar size shortcode attribute issue in donor wall shortcode and adds support for avatar size in donor wall block (#5443)
-   Onboarding Form Preview template now loads scripts inside of the closing body tag (#5510)
-   Deprecated e2e tests have been removed, and replaced with Cypress tests (#5533)
-   New `test:e2e` package script introduced (#5533)
-   Decouple test:e2e command from wp-env #5545
-   Unit test structure is refactored (#5554)

### Fixed

-   Show field icons on personal information input field when payment gateway switch (#5542)
-   Multiple error redirects does not break donation form view in Multi Step form template view (#5531)
-   Hover glitches of Fee Recovery checkbox are now fixed (#5508)
-   PayPal Donations CC fields have border in Firefox browser (#5500)
-   Send form title to PayPal (#5495)
-   Automated unit and integrations tests are now executing (#5489)
-   Use an absolute path for the autoloader to avoid relative path issues (#5493)
-   Donation Receipt accounts for latency in payment status changes (#5514)
-   Create an account checkbox only displays when Guest Checkout is enabled (#5516)
-   Error messages are instructive when an email address is already in the system (#5504)
-   The current state of the Donation Form fields are now preserved when the payment method changes (#5491)
-   Various Multi-Step form browser styling compatibility issues are now resolved (#5529)
-   Checkbox click handler does not double trigger for touch devices (#5526)
-   Multi-Form Goals added via shortcode now stack image and text when needed (#5528)
-   Added migration to remove any leftover foreign keys on the revenue table (#5540)
-   Give Aid add-on description popup is now working when using multi-step form (#5549)

## 2.9.5 - 2020-12-03

### New

-    Onboarding locales now include Jamaica (#5474)

### Fixed

-   Currency Switcher options are visible in the dropdown on Windows machines (#5453)
-   Prevent iOS from adding glare and pill-style rounded corners to the multi-step form template (#5438)
-   Restored compatibility with < WP 5.1.1 (#5473)

## 2.9.4 - 2020-11-20

### Fixed

-   Donation id mentions in exception and log message when insert query for revenue table fails  (#5472)
-   Allow revenue with a 0 amount to be inserted  (#5472)
-   Prevent fatal error when delete donation on WP < 5.5.0 (#5470)
-   Stripe single-input credit card field works again (#5469)
-   Updating a Stripe subscription from the update payment info screen works again (#5467)

## 2.9.3 - 2020-11-17

### Fixed

-   Corrected a warning being thrown by the MigrationRunner (#5457)
-   Stripe CC donations now work when not the default gateway (#5459)
-   Stripe modal checkout donations now work when not the default gateway (#5459)

## 2.9.2 - 2020-11-09

### New

-   Added support method of running migrations and clearing updates (#5447)

### Fixed

-   Properly handle currency unit size for donations (#5440)
-   Add missing payment information update support in Stripe javascript (#5439)
-   Free add-ons does not trigger GiveWP add-on license errors (#5424)
-   Stripe Modal renders without any issue across all screens (#5423)
-   Restore Donate Now button and show donor error after Stripe returns error when create payment method (#5421)
-   Stripe Checkout payment method does not cause of javascript error on donation form page (#5419)
-   Multi-step form loader color is incorrectly the default green color when embedded. (#5436)
-   Multi-Form Goal Shortcode now supports comma separated lists (#5432)
-   Remove foreign keys from revenue table for MyISAM support (#5447)

### Changed

-   Use easy digital download rest api endpoint to confirm if the add-on is premium or not (#5426)

### 2.9.1 - 2020-10-28

### Fixed

-   Restore PayPal Standard functionality that was affected by a name change in GiveWP 2.9.0 (#5414)
-   Prevent Onboarding wizard and setup from displaying for WP < 5.x (#5416)
-   Retain WP 4.9 compatibility by preventing block registration (#5416)

## 2.9.0-rc.1 - 2020-10-27

### Added

-   Upgrade notice now clarifies the purpose of the 2.9.0 database upgrade

### Fixed

-   Imported donations now store the correct revenue amount (#5407)
-   Imported donations do not affect donation levels anymore (#5410)

### Changed

-   Plugin's readme.txt is updated with new content for the Plugin Directory (#5413)

## 2.9.0-beta.4 - 2020-10-26

### Fixed

-   Avoid filename collisions when exporting users and donations (#5346)
-   Reports for "All Time" are now inclusive of the first day (#5400)
-   Fix PayPal Donation webhooks in live mode (#5403)

## 2.9.0-beta.3 - 2020-10-22

### Added

-   Past donation data migration for revenue database table is compatible with currency switcher (#5382)

### Fixed

-   Tabbing through credit card fields now works in the expected order (#5380)
-   Long text overflowing outside of the container in Donation receipt (#5390)
-   Legacy form loading spinner (#5397)
-   Multi-Form Goal end date now reflects time zone (#5394)
-   Field description text is now accessible and aligned (#5396)

## 2.9.0-beta.2 - 2020-10-19

### Added

-   Company Name can now be displayed on Donor Wall (#5374)
-   Support for multi-line radio options (#5383)

### Fixed

-   Load PayPal SDK only on a page that has a donation form (#5376)
-   Disconnecting a Stripe account no longer revokes GiveWP as an Authorized Application (#5378)

## 2.9.0-beta.1 - 2020-10-13

### Fixed

-   Set composer platform PHP version to 5.6 to ensure package compatibility (#5266)

### Changed

-   Multi-Form Goal Blocks now auto-focus the Progress Bar on insert (#5364)
-   Improve PayPal Donations payment gateway setting page UX ( #5369 )
-   Multi-Form Goal query results are cached (#5371)
-   Update composer setup (#5361)

## 2.9.0-alpha.2 - 2020-10-09

### Added

-   PayPal Donations is a new payment gateway (#5079)
-   PayPal Donations supports currency switcher (#5335)
-   PayPal Donations supports subscriptions (#5173, #5221, #5308)

### Changed

-   Reports page main menu is now extendable ( #5339 )
-   Multi-Form Goal Progress Bar only shows "time to go" if the end date has not passed (#5350)
-   Multi-Form Goal Block uses the Revenue table to calculate progress towards a goal. (#5357)
-   Multi-Form Goal total includes renewals (#5359)
-   Multi-Form Goal Progress Bar styles are encapsulated via a Shadow DOM (#5348)

### Fixed

-   Multi-Form Goal block no longer obscure column controls (#5352)

## 2.9.0-alpha.1 - 2020-10-06

### Added

-   Migrations framework for database migrations
-   Multi-Form Goal wrapper only added for non-block output (#5315)
-   Multi-Form Goal output has a bottom margin (#5333)
-   Multi-Form Goal end date now allows for specific time (#5336)
-   Progress Bar block is no longer available outside of Multi-form Goal (#5338)
-   Multi-Form Goal Block now defaults to image filling the height (#5314)
-   Multi-Form Goal Block metric calculations are more performant (#5345)
-   Migrations framework can now be used for more reliable database migrations.
-   Multi-Form Goal block and shortcode are now available. (#5307)
-   Multi-Form Goal block now supports "wide" alignment. (#5315)
-   Multi-Form Goal block now supports the theme's color palette. (#5319)
-   Multi-Form Goal block and shortcode appearance is now consistent. (#5320)
-   New database table handles revenue independently from donations. (#5257)

### Changed

-   Milestone block is now known as the Multi-form Goal block.
-   Multi-Form Goal wrapper only added for non-block output. (#5315)
-   Multi-Form Goal output has a bottom margin. (#5333)
-   Multi-Form Goal end date now allows for specific time. (#5336)
-   Multi-Form Goal Block now defaults to image filling the height. (#5314)
-   Introduced Currency Switcher compatibility styles for the Multi-Step form (#5220)

## 2.8.1 - 2020-10-08

### Fixed

-   Donations now process for non-US countries using billing details (#5355)

## 2.8.0-rc.1 - 2020-08-31

### Fixed

-   Resolved a conflict with the User Avatar plugin due to improper HTML output of the user profile field markup. (#5218)
-   PHP Notices no longer break multi-step form receipt step. (#5219)
-   Fee Recovery checkbox placement in Multi-Step forms now respects the Fee Recovery input location setting. (#5205)
-   Form Field Manager fields are now set up on init of the Multi-Step form to ensure they work with only a single gateway enabled. (#5216)

## 2.8.0-beta.3 - 2020-08-27

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

## 2.8.0-beta.2 - 2020-08-25

### Fixed

-   Trailing comma in function call is removed for PHP 5.6 support. (#5195)
-   Fixed translation of common text to support WordPress 5.5, with backwards compatibility for `commonL10n`. (#5186)

## 2.8.0-beta.1 - 2020-08-24

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

## 2.8.0-alpha.2 - 2020-08-19

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

## 2.8.0-alpha.1 - 2020-08-17

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
