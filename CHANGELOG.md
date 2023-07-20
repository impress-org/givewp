# Changelog

Since GiveWP 2.8.0, all notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

## New
-   Allow Fields API fields to be macroable (#5900)

## Fixed

- Auto set new settings if old settings exist ("Primary Color", "Decimal Enabled"). (#5973)

## 2.16.0 - 2021-10-26

## 2.16.0-rc.1 - 2021-10-25

### New

- Make field name required in field api. (#6032)
- Add option to disable google fonts in multi-step form template. (#5973)
- Add common style file for form field render with legacy consumer. (#6059)

### Changed

- Add visibility conditions to field container to simplify field display logic in field api. (#6024)
- Use field size in bytes in field api to get precise result when compare field size. (#6024)
- Return empty array if file does not exist in $_FILES in field api. (#6032)
- Make field required only if visible in donation form in field api.
- Default value is now used in Legacy Consumer textarea template. (#6043)
- Use legacy consumer common style to style form fields in multi-step form template. (#6059)

### Fixed

- Give icons in Gutenberg block admin UI now display correctly in Firefox (#6023)
- An error message that was confusing has been clarified (#6045)
- PayPal IPN validation error when using PayPal Standard Gateway and GiveWP test mode (#6057)
- Show minimum donation amount notice in donation form. (#6059)

## 2.15.0 - 2021-10-11

### New

- Option to make Donor Last Name filed required is now implemented (#6004)

### Changed

- Preview Emails are now sent to the authenticated user (#5990)
- Donor dashboard response messages are now updated (#6003)
- Donor dashboard authentication modal only displays the login option if there are forms that allow a donor to register
  and/or login to an account (#6015)
- PayPal IPN verification setting with backward compatibility. PayPal IPN verification is enabled by default. (#5986)
- Remove unnecessary checks from PayPal web payment ipn verification. (#5986)

### Fixed

- Admin can switch donation status on PHP8.0. (#5971)
- DI Container auto wiring now works correctly when using PHP 8 (#5988)
- Single donors can now be deleted via the donors table (#5992)
- Donors can now properly set custom amounts in the donor dashboard (#6001)
- Format amount correctly in 'Lifetime Donations' and 'Average Donation' in donation dashboard. (#5998)
- Resolve PHP 5.6 compatibility issue when run any WP cli command. (#6005)
- Add change event to multi checkbox options correctly to field api fields. (#6009)
- Resolve checkbox and radio (generated with field api) state related issues in Multi Step Form template. (#6013)
- Refactor setup logic of checkbox and radio in Multi Step Form template. (#6013)
- Show custom payment gateway label in donation form. (#6012)
- Conditionally display offline donation instructions based on form settings (#6020)
- Dom element do not display in donation form if it has give-hidden class. (#6017)

## 2.14.0 - 2021-09-27

### Fixed

- Errors when connecting to Stripe using PHP 8 are now resolved (#5978)
- Prevent multiple donations on mobile devices (#5983)

## 2.14.0-beta.1 - 2021-09-21

### Changed

- Show correct placeholder in legacy consumer in input field template. (#5924)
- Do not allow to choosing placeholder in legacy consumer in select field template if field is required. (#5924)
- Remove `give_` prefix from field names in legacy consumer in select, radio and textarea field template. (#5924)
- Set select field name to array type in legacy consumer for select field template if multiple value acceptable field
  template. (#5924)
- Convert array type values to pipe (|) separated string before make custom field value persistence. (#5933)
- Move custom field validation to `give_checkout_error_checks` hook. (#5933)
- Set WordPress default file max_size and allowed file type as default value of file custom field in FieldAPI. (#5933)
- Custom fields have unique ID attribute in legacy consumer. (#5938)
- Form field manager custom fields rendered with field api look good in multi-step form template. (#5946)
- Use add meta function to persist field value in field api. (#5954)
- Use WP time format in donor dashboard. (#5975)

### New

- Add `give_form_{ID}_field_classes_{fieldName}` hook to legacy consumer for setting classes on field wrapper. (#5917)
- File type custom field added by legacy consumer should persist when donation completes. (#5933)
- Add support for multiple file upload in legacy consumer for file template. (#5933)
- Add `enctype` attribute to form if file type custom field added to donation form. (#5933)
- Pass form id to donation form action url which help to show notices from session. (#5933)
- Add hooks to legacy consumer to handle rendering, validating and saving for custom fields. (#5944)
- Add min/max-length validation to text Fields API node types. (#5948, #5955)
- Implement conditional visibility support in Legacy Consumer. (#5966)
- Add conditional visibility support to field type HTML in legacy consumer. (#5968)
- Add options support to checkbox field. (#5968)
- Update checkbox field template to render multiple checkbox in legacy consumer. (#5968)
- Make checkbox discoverable in visibility condition javascript. (#5968)
- The "Thank You" section description now supports HTML content. (#5985)
- The "Thank You" description setting field update from textarea to wysiwyg. (#5985)

### Fixed
- Add min/max-length validation to text and textarea Fields API node types. (#5955)
- Add maxlength attribute in input and textarea field template in legacy consumer. (#5955)
- Prevent php notices which generate from offline -donations.php. (#5960)
- Formatting button display correctly when decimals enabled in Multi-Step Form. (#5957)
- Donor addressed is now spaced out on the Donor Dashboard receipt (#5961)
- Social sharing is now fixed (#5964)
- Payment ID in donation email previews correctly reflects donation sequence ID. (#5967)
- Resolve PHP 5.6 compatibility issue when run any WP cli command. (#5981)
- Show admin defined checkout payment gateway label. (#5980)

## 2.13.4 - 2021-09-03

### Fixed

- Onboarding form donation level migration no longer clears extra donation level data (#5950)

## 2.13.3 - 2021-09-01

### Fixed

- Donation level id and amount value store as string for donation form generate in on-boarding process. (#5940)
- Set focus on donation amount field when click on "Custom" donation level id. (#5943)

## 2.13.2 - 2021-08-26

## Fixed

- Admin able to create donation form and edit existing donation form if stripe disabled. (#5935)

## 2.13.1 - 2021-08-20

### Fixed

- Custom amount level is correctly set after a payment error (#5922)
- Lodash is now an external dependency (#5925)

## 2.13.0 - 2021-08-19

### New

- Completely new and fresh UI for managing Stripe accounts (#5832)
- Add conditional visibility functionality to `FieldsAPI` `Field`, `Element`, and `Group` types (#5919)
- Introduce `BasicCondition` and `NestedCondition` classes for expressing conditional logic in the Fields API (#5919)

### Changed

- Add missing help text tooltip to Legacy Consumer’s label content templates. (#5921)
- Wrap `<input>` element inside of `<label>` element for Legacy Consumer’s checkbox template. (#5920)
- Add missing `required` and `readonly` attributes to Legacy Consumer’s select and textarea templates. (#5920)
- Add screen reader text for required indicator to Legacy Consumer’s label content template. (#5920)

## 2.12.3 - 2021-08-12

### Fixed

- Ensure the Primary Color picker is still available when Step 1 is disabled for the multi-step donation form
  template. (#5910)
- Fix the placeholder image and set a max-width for the legacy form template. (#5910)
- Add support for admin defined recurring donations to the multi-step form template. (#5910)
- Fix style issue with Authorize.net eCheck on the multi-level donation form. (#5910)
- Payment errors no longer revert to the default donation amount (#5913)
- Removed an undefined variable error from being set as the class name for textareas rendered by the Legacy Consumer (
  #5918)
- Donors can now update their credit card using the Donor Dashboard (#5902)

## 2.12.2 - 2021-07-30

### Added

- Added optional opt-in to onboarding wizard. (#5852)
- Add `Html` node type to Fields API (#5898)

### Fixed

- Add missing `TYPE` to Fields API `Group` node type (#5895)
- Set validation rules correcetly for Fields API `File` (#5892)
- Add missing Fields API node types to `Types` (#5891)
- Remove placeholder from Legacy Consumer checkbox template (#5897)
- Use correct ID in Legacy Consumer checkbox label for attribute (#5897)
- Donors with no donations no longer see others (#5908)

### Changed

- Add `HTML` constant to `Give\Framework\FieldsAPI\Types` for `Html` node type (#5898)
- Support multi-selects in Legacy Consumer select template (#5905)

### Changed

-   Add `HTML` constant to `Give\Framework\FieldsAPI\Types` for `Html` node type (#5898)
-   Do not extend Fields API `Form` from `Group` (#5889)

## 2.12.1 - 2021-07-22

### Fixed

- Filtered donation level text no longer breaks form HTML (#5894)

## 2.12.0 - 2021-07-21

### Added

- Add Facade class to give framework (#5855)

### Fixed

- Update wp-env package to resolve project setup issue (#5850)
- Fix "Unsupported declare strict_types" PHP warning (#5853, #5869)
- Add top margin to setting group page (#5864)
- Add custom donation level choice to select field if donation donated with custom amount (#5866)
- Card declines on multi-step form now display an error message on first click (#5868)
- GiveWP is not causing deprecation warnings on PHP8 anymore (#5872)

### Changed

- Update field descriptions to be more legible for accessibility (#5875)
- Refactor `Give\Framework\FieldsAPI` to include classes for each node type (#5843, #5885, #5890)
- Legacy Consumer no longer adds custom field values to payment details automatically (#5886)
- Fields API factory now reflects individual type classes (#5887)

## 2.11.3 - 2021-07-06

### Fixed

- Prevent givewp.com downtime from affecting customer sites (#5863)

## 2.11.2 - 2021-06-08

### Fixed

- Uncaught exception handler no longer breaks on Errors (#5846)
- Uninstalling GiveWP no longer throws an exception (#5846)
- Caught GiveWP exceptions no longer display a white screen (#5861)

## 2.11.1 - 2021-05-24

### New

- Add billing address field support to PayPal Donations (#5744)

### Fixed

- Donor Dashboard can now be translated using the `i18n.gettext` filter (#5842)

## 2.11.0 - 2021-05-19

### New

- Multi-Step form template now supports decimals amounts in currencies (#5827)
- Donor Dashboard is now fully translatable (#5819)

### Changes

- The Indian state of Odisha (formerly Orissa) is now updated to reflect the legal name change (#5826)
- Onboarding LocaleCollection has parity with WooCommerce (#5831)
- Remove custom Stripe SDK loading logic (#5821)

### Fixed

- Store donor address with formatting from donor profile (#5829)
- Prevent javascript error when click on lock icon on email listing page (#5824)
- Amount passed to Stripe matches the stored value (#5823)
- Remove extraneous main landmarks from setup guide page (#5835)
- Admin is now can import donations (#5841)
- Prevent PHP notices when access non-exiting property from Stripe API response (#5838)

## 2.10.4 - 2021-04-29

### Changes

- Deprecate give_currencies filter hook (#5782)
- PHAR files are stripped from builds (#5811)
- Correct usage of file_get_contents for remote request in favor of WP HTTP functions (#5811)

### Fixed

- Conditional check for is_give_form() now uses correct post type slug (#5807)
- Added missing escaping for improved security (#5811)

## 2.10.3 - 2021-04-21

### Fixed

- Donor Dashboard Logout now works while in the same session (#5800)
- SubscriptionsTable component no longer produces console error (#5793)
- Test Donation badge now only appears on test donations in Donor Dashboard (#5803)
- Donors without WP accounts are now able to upload avatar images (#5745)

### Changed

- Give Session now reliably reflects currently logged in WP user (#5796)
- Donor Dashboard now uses WP API nonces for enhanced security (#5798)

## 2.10.2 - 2021-04-14

### Changed

- Apply Stripe fee when applicable except Brazil (#5729)

### Fixed

- Donor Dashboard no longer freezes up when attempting to manage Stripe ACH subscriptions (#5771)
- Logs table creation is now backward compatible with MySQL 5.6 (#5776)
- Donors can no longer see other donors donations (on certain hosts) (#5787)
- Logs will no longer cause an exception (#5788)
- Donor Dashboard page is now only generated if one does not already exist (#5785)

## 2.10.1 - 2021-03-30

### Fixed

- Prevent fatal error because of param declaration compatibility (#5769)
- Stop revenue migration on error (#5748)
- Fix broken link by correctly closing href tag (#5746)
- Prevent PHP notice on system info page (#5606)
- GiveWP Database management tools are now backward compatible with MySQL 5.6 (#5759)
- Composer dependencies now reference releases instead of branches (#5763)
- Donor search no longer shows undefined index notice (#5752)
- Retrieve migrations only when necessary (#5760)
- Donors are now able to log into Donor Dashboard on hosts with page caching (#5766)
- The Donor Dashboard are no only be generated in admin-side pages (#5768)

## 2.10.0 - 2021-03-22

### Changed

- Donor Dashboard UI is now consistent and polished (#5741)

### Fixed

- The Donor Dashboard shortcode now works on 4.9.x (#5739)

## 2.10.0-rc.2 - 2021-03-18

### Fixed

- Personal information section only reloads if condition met (#5727)
- Editing the recurring donation amount no longer displays "NaN" (#5735)
- Creation of the give_migrations table is not producing errors anymore (#5737)
- Logging can no longer cause a fatal error to occur (#5737)

## 2.10.0-rc.1 - 2021-03-16

### New

- The new logging system UI is now e2e tested (#5723)

### Changed

- Apply Stripe fee when applicable (#5555)
- Cleanup and update deprecated npm packages (#5712)

### Fixed

- Donor Dashboard history now includes pending donations (#5721)
- Multi Form Goal Shortcode class is handleing the empty attributes correctly now (#5716)
- The spinner should go away when the PDF Receipt is generated on the Donor Dashboard (#5719)
- "Make Primary" address link for multiple addresses now works in Donor Dashboard (#5725)

### Changed

- DonorProfiles domain namespace is now DonorDashboards (#5728)

## 2.10.0-beta.4 - 2021-03-12

### Changed

- "Donor Profile" is now renamed "Donor Dashboard" (#5708)

### Fixed

- Prevent PHP notices on plugin listing page (#5692)
- Remove login form after a successful login in donation form with multi step form template (#5683)
- Donors with no donations now see a "No Donations" notice in Donor Dashboard (#5694)
- Donor Dashboard iframe now resizes when the parent window resizes (#5693)
- Migration table id column length is now increased (#5698)
- Incorrect property type error is now fixed (#5699)
- Donor Dashboard feature domain no longer has typos (#5704)
- Email access now gives donor access to my donor dashboard (#5705)
- While in the same session, donors are now able to see their donor dashboard (#5707)

### New

- Donor Dashboard errors are now logged (#5706)

## 2.10.0-beta.3 - 2021-03-09

### Changed

- Donor Dashboard UI is now more polished (#5686)

### Fixed

- Donors now only ever see their own donation history (#5676)
- Usage tracking job scheduler should not reduce performance (#5678)
- Render donor dashboard react app only if container exist (#5684)

## 2.10.0-beta.2 - 2021-03-05

### New

- Donor Profile Verify Email now supports reCAPTCHA (#5661)

### Changed

- Form Consumer now limit hooks and instantiated classes (#5670)
- Donor Profile receipt content now match receipt displayed during donation (#5666)

### Fixed

- Legacy log migrations are not failing on fresh install now (#5672)
- Custom Fields on the Sequoia template now match alignment (#5669)

## 2.10.0-beta.1 - 2021-03-02

### New

- Anonymous donation and company setting choices are now persisted (#5633)
- Admin are now shown Donor Profiles upgrade notice (#5660)
- Donor Profile page is now generated on new installs (#5600)
- Custom field values now display in payment details (#5647)
- Custom field values now show in Donation Receipt and Donation Confirmation (#5654)
- Fields API now supports custom field email tags (#5649)
- Add non-sensitive information tracker. (#5658)

### Fixed

- Custom Fields on the Sequoia template now match alignment (#5669)
- Add since-unreleased script for updating @since 2.27.1 docblocks. (#5602)
- Reports endpoints no longer throw an error due to missing $schema property (#5642)
- Zip Code required field indicator now toggles correctly for the Legacy Form template (#5627)

### Changed

- Fields API throws an exception when inserting relative to a node that does not exist (#5640)
- Form Templates now support custom checkbox fields. (#5643)
- Custom fields now support arbitrary attributes (#5641)

## 2.10.0-alpha.2 - 2021-02-25

### New

- PDF Receipts are now available from Donation Receipt pages (#5613)
- Annual Receipts UI now connects to addon (#5611)
- Anonymous donation and company setting choices are now persisted (#5633)

### Changed

- MigrationsRunnerTest unit test has been removed (#5653)
- Donor Profile UI is polished to align with original designs (#5648)

## 2.10.0-alpha.1 - 2021-02-19

### New

- Front-end donor profiles are scaffolded (#5441)
- Donor Profiles UI is implemented (#5444)
- Donation History and Dashboard tabs are now dynamically populated (#5455)
- Edit Profile UI is now implemented (#5463)
- Edit Profile tab now persists data (#5486)
- Recurring Donations tab UI now connects to addon (#5584)
- Donor Profile authentication logic is now implemented (#5569)
- Admin can now set Donor Profile accent color (#5615)
- Migrations table (#5580)
- Legacy functions used for logging are now updated to use the new Logging API (#5614)
- The new Logs UI is implemented (#5591)
- Add since-unreleased script for updating @since 2.27.1 docblocks. (#5602)

### Changed

- Donor Profile Donation History UI is now polished (#5566)
- Donor Profile edit UI now uses React Dropzone (#5563)
- Donor Profiles are now extensible (#5577)

## 2.9.7 - 2021-02-09

### Fixed

- Create account checkbox is hidden when guest registration is disabled (#5557)
- Using the CLI commands is not producing errors anymore (#5559)
- Multi Form Goal is not producing errors and warnings when used as a Divi module (#5565)
- Setup Page gateway links now render styles correctly (#5576)

## 2.9.6 - 2021-01-13

### New

- Update warning message for unsuccessful PayPal account onboarding (#5522)
- Add filter hook to filter PayPal settings (#5502)
- Sample onboarding tests are now implemented (#5543)
- Onboarding wizard e2e tests are now implemented (#5550)
- Setup page is e2e tested (#5547)

### Changed

- Automated unit and integrations tests are now using GitHub actions, instead of Travis CI (#5489)
- Resolve Avatar size shortcode attribute issue in donor wall shortcode and adds support for avatar size in donor wall
  block (#5443)
- Onboarding Form Preview template now loads scripts inside of the closing body tag (#5510)
- Deprecated e2e tests have been removed, and replaced with Cypress tests (#5533)
- New `test:e2e` package script introduced (#5533)
- Decouple test:e2e command from wp-env #5545
- Unit test structure is refactored (#5554)

### Fixed

- Show field icons on personal information input field when payment gateway switch (#5542)
- Multiple error redirects does not break donation form view in Multi Step form template view (#5531)
- Hover glitches of Fee Recovery checkbox are now fixed (#5508)
- PayPal Donations CC fields have border in Firefox browser (#5500)
- Send form title to PayPal (#5495)
- Automated unit and integrations tests are now executing (#5489)
- Use an absolute path for the autoloader to avoid relative path issues (#5493)
- Donation Receipt accounts for latency in payment status changes (#5514)
- Create an account checkbox only displays when Guest Checkout is enabled (#5516)
- Error messages are instructive when an email address is already in the system (#5504)
- The current state of the Donation Form fields are now preserved when the payment method changes (#5491)
- Various Multi-Step form browser styling compatibility issues are now resolved (#5529)
- Checkbox click handler does not double trigger for touch devices (#5526)
- Multi-Form Goals added via shortcode now stack image and text when needed (#5528)
- Added migration to remove any leftover foreign keys on the revenue table (#5540)
- Give Aid add-on description popup is now working when using multi-step form (#5549)

## 2.9.5 - 2020-12-03

### New

- Onboarding locales now include Jamaica (#5474)

### Fixed

- Currency Switcher options are visible in the dropdown on Windows machines (#5453)
- Prevent iOS from adding glare and pill-style rounded corners to the multi-step form template (#5438)
- Restored compatibility with < WP 5.1.1 (#5473)

## 2.9.4 - 2020-11-20

### Fixed

- Donation id mentions in exception and log message when insert query for revenue table fails  (#5472)
- Allow revenue with a 0 amount to be inserted  (#5472)
- Prevent fatal error when delete donation on WP < 5.5.0 (#5470)
- Stripe single-input credit card field works again (#5469)
- Updating a Stripe subscription from the update payment info screen works again (#5467)

## 2.9.3 - 2020-11-17

### Fixed

- Corrected a warning being thrown by the MigrationRunner (#5457)
- Stripe CC donations now work when not the default gateway (#5459)
- Stripe modal checkout donations now work when not the default gateway (#5459)

## 2.9.2 - 2020-11-09

### New

- Added support method of running migrations and clearing updates (#5447)

### Fixed

- Properly handle currency unit size for donations (#5440)
- Add missing payment information update support in Stripe javascript (#5439)
- Free add-ons does not trigger GiveWP add-on license errors (#5424)
- Stripe Modal renders without any issue across all screens (#5423)
- Restore Donate Now button and show donor error after Stripe returns error when create payment method (#5421)
- Stripe Checkout payment method does not cause of javascript error on donation form page (#5419)
- Multi-step form loader color is incorrectly the default green color when embedded. (#5436)
- Multi-Form Goal Shortcode now supports comma separated lists (#5432)
- Remove foreign keys from revenue table for MyISAM support (#5447)

### Changed

- Use easy digital download rest api endpoint to confirm if the add-on is premium or not (#5426)

### 2.9.1 - 2020-10-28

### Fixed

- Restore PayPal Standard functionality that was affected by a name change in GiveWP 2.9.0 (#5414)
- Prevent Onboarding wizard and setup from displaying for WP < 5.x (#5416)
- Retain WP 4.9 compatibility by preventing block registration (#5416)

## 2.9.0-rc.1 - 2020-10-27

### Added

- Upgrade notice now clarifies the purpose of the 2.9.0 database upgrade

### Fixed

- Imported donations now store the correct revenue amount (#5407)
- Imported donations do not affect donation levels anymore (#5410)

### Changed

- Plugin's readme.txt is updated with new content for the Plugin Directory (#5413)

## 2.9.0-beta.4 - 2020-10-26

### Fixed

- Avoid filename collisions when exporting users and donations (#5346)
- Reports for "All Time" are now inclusive of the first day (#5400)
- Fix PayPal Donation webhooks in live mode (#5403)

## 2.9.0-beta.3 - 2020-10-22

### Added

- Past donation data migration for revenue database table is compatible with currency switcher (#5382)

### Fixed

- Tabbing through credit card fields now works in the expected order (#5380)
- Long text overflowing outside of the container in Donation receipt (#5390)
- Legacy form loading spinner (#5397)
- Multi-Form Goal end date now reflects time zone (#5394)
- Field description text is now accessible and aligned (#5396)

## 2.9.0-beta.2 - 2020-10-19

### Added

- Company Name can now be displayed on Donor Wall (#5374)
- Support for multi-line radio options (#5383)

### Fixed

- Load PayPal SDK only on a page that has a donation form (#5376)
- Disconnecting a Stripe account no longer revokes GiveWP as an Authorized Application (#5378)

## 2.9.0-beta.1 - 2020-10-13

### Fixed

- Set composer platform PHP version to 5.6 to ensure package compatibility (#5266)

### Changed

- Multi-Form Goal Blocks now auto-focus the Progress Bar on insert (#5364)
- Improve PayPal Donations payment gateway setting page UX ( #5369 )
- Multi-Form Goal query results are cached (#5371)
- Update composer setup (#5361)

## 2.9.0-alpha.2 - 2020-10-09

### Added

- PayPal Donations is a new payment gateway (#5079)
- PayPal Donations supports currency switcher (#5335)
- PayPal Donations supports subscriptions (#5173, #5221, #5308)

### Changed

- Reports page main menu is now extendable ( #5339 )
- Multi-Form Goal Progress Bar only shows "time to go" if the end date has not passed (#5350)
- Multi-Form Goal Block uses the Revenue table to calculate progress towards a goal. (#5357)
- Multi-Form Goal total includes renewals (#5359)
- Multi-Form Goal Progress Bar styles are encapsulated via a Shadow DOM (#5348)

### Fixed

- Multi-Form Goal block no longer obscure column controls (#5352)

## 2.9.0-alpha.1 - 2020-10-06

### Added

- Migrations framework for database migrations
- Multi-Form Goal wrapper only added for non-block output (#5315)
- Multi-Form Goal output has a bottom margin (#5333)
- Multi-Form Goal end date now allows for specific time (#5336)
- Progress Bar block is no longer available outside of Multi-form Goal (#5338)
- Multi-Form Goal Block now defaults to image filling the height (#5314)
- Multi-Form Goal Block metric calculations are more performant (#5345)
- Migrations framework can now be used for more reliable database migrations.
- Multi-Form Goal block and shortcode are now available. (#5307)
- Multi-Form Goal block now supports "wide" alignment. (#5315)
- Multi-Form Goal block now supports the theme's color palette. (#5319)
- Multi-Form Goal block and shortcode appearance is now consistent. (#5320)
- New database table handles revenue independently from donations. (#5257)

### Changed

- Milestone block is now known as the Multi-form Goal block.
- Multi-Form Goal wrapper only added for non-block output. (#5315)
- Multi-Form Goal output has a bottom margin. (#5333)
- Multi-Form Goal end date now allows for specific time. (#5336)
- Multi-Form Goal Block now defaults to image filling the height. (#5314)
- Introduced Currency Switcher compatibility styles for the Multi-Step form (#5220)

## 2.8.1 - 2020-10-08

### Fixed

- Donations now process for non-US countries using billing details (#5355)

## 2.8.0-rc.1 - 2020-08-31

### Fixed

- Resolved a conflict with the User Avatar plugin due to improper HTML output of the user profile field markup. (#5218)
- PHP Notices no longer break multi-step form receipt step. (#5219)
- Fee Recovery checkbox placement in Multi-Step forms now respects the Fee Recovery input location setting. (#5205)
- Form Field Manager fields are now set up on init of the Multi-Step form to ensure they work with only a single gateway
  enabled. (#5216)

## 2.8.0-beta.3 - 2020-08-27

### Added

- Multi-step forms now support RTL styles. (#5196)

### Fixed

- Deprecated jQuery warnings no longer appear when jQuery Migrate Helper plugin is active. (#5184)
- Multi-step form anonymous donation checkbox is now checkable after changing the payment gateway. (#5191)

### Changed

- Onboarding Form Preview default image has been updated. (#5203)
- Stripe Checkout modal max-width has been increased to fit-content. (#5209)
- If the Setup Page is disabled, Onboarding Wizard now directs users to the All Forms page. (#5211)
- On a fresh install, the donation forms archive is now enabled by default. (#5214)
- Specify Form Route URL scheme to avoid mixed content when loaded in the admin. (#5189)

## 2.8.0-beta.2 - 2020-08-25

### Fixed

- Trailing comma in function call is removed for PHP 5.6 support. (#5195)
- Fixed translation of common text to support WordPress 5.5, with backwards compatibility for `commonL10n`. (#5186)

## 2.8.0-beta.1 - 2020-08-24

### Changed

- Stripe Checkout modal is now rendered using Stripe Elements so that users can continue to use the modal display style
  even after it is deprecated by Stripe. (#4964)
- Format for country and state select fields is normalized so states have an empty option but countries do not. (#5163)
- Scope of marked optional fields in the Multi-Step template is reduced to the User Info fieldset. (#5161)
- Wizard buttons now match the form preview. (#5167)
- Setup Page now initiates the connection to Stripe, but defers webhook configuration to the gateway settings. (#5171)
- Removed preg_match that prevented version numbers with tags from being stored (#5172)
- Admin notice animation has been removed. (#5182)
- Setup Page margins are now consistent with other GiveWP admin pages. (#5180)
- Version numbers with tags (e.g. `2.8.0-beta.1`) can now be saved in full to the database. (#5172)

### Fixed

- Placeholder for the Base Country setting no longer reads "Select a form". (#5163)
- Form preview within the Onboarding Wizard now remains centered on larger viewports. (#5180)
- Onboarding Wizard no longer shows empty submenu under Dashboard. (#5190)

## 2.8.0-alpha.2 - 2020-08-19

### Changed

- Add-ons listed on the Setup Page are now denoted as suggestions based on selections made in the Wizard. (#5145)
- Setup Page links now use short URLs that can be changed without updating the plugin. (#5146)
- Stripe colors in the Setup Page are further differentiated from PayPal. (#5148)
- Cause Types presented in the Wizard now include full list of options. (#5141)
- Wizard feature for "One-Time Donations" is replaced by "Offline Donations". (#5103)
- Wizard now prompts when exiting without completing required steps. (#5111)
- Optional fields in the Multi-Step form template are denoted to appear distinct from required fields. (#5157)
- Default minimum donation amount is increased from $1.00 to $5.00 to help prevent card testing spam. (#5120)

### Fixed

- Clickable elements in the Wizard now denoted visually with a cursor pointer. (#5127)
- Wizard now maintains a consistent width when scrolling is toggled due to changes in page height. (#5107)
- Setup Page header logo now aligns with content container. (#5135)
- Setup Page assets now load from the correct directory in production. (#5108)
- Missing block links in Setup Page now added. (#5128)
- Location settings in the Wizard now default to current setting value. (#5150)
- Resolved style and JS issues in WordPress 5.5+ with GiveWP's WP-admin metabox expand/collapse and repeater elements. (
  # 5126)

## 2.8.0-alpha.1 - 2020-08-17

### Added

- New Onboarding Wizard guides new users through first-time configuration. (#5014)
- New Setup Page clarifies required steps that must be completed prior to accepting live donations. (#5014)
- New `CHANGELOG.md`, Keep a Changelog, and Semantic Versioning standards are now in place. (#5117)
- Update Stripe Checkout to use Stripe Elements. (#4964)

### Changed

- First-time installs now redirect the user to the Onboarding Wizard which can be dismissed. (#5014)

### Removed

- Old Welcome Page has been removed in favor of the new Onboarding Wizard & Setup Page. (#5014)

### Fixed

- The `[give_receipt]` shortcode is more compatible alongside other shortcodes, which is especially relevant for page
  builders. (#5044)
- A `register_rest_route` notice no longer displays when creating a new page in the block editor. (#5115)
- A typo in the Terms & Conditions field description has been fixed. (#5110)
- Installed version of PHPUnit now supports PHP 5.6. (#5100)
