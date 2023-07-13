=== GiveWP - Visual Donation Form Builder ===
Contributors: givewp, dlocc, webdevmattcrom
Donate link: https://givewp.com/
Tags: givewp, donation, donations, donation plugin, wordpress donation plugin, wp donation, donors, display donors, give donors, anonymous donations
Requires at least: 5.9
Tested up to: 6.2
Requires PHP: 7.2
Stable tag: 0.5.0
Requires Give: 2.30.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create the donation form of your dreams using an easy-to-use visual donation form builder.

= 0.5.0: July 12th, 2023 =
* Feature: The give_form shortcode now supports v3 forms
* Feature: Added a Terms and Conditions block
* Feature: Added a Billing Address block
* Feature: Added Reveal and Modal display options to the v3 Donation Form block v3
* Feature: A selected v3 form is rendered in the block editor
* Refactor: The Payment Gateway API is now compatible with v3
* Refactor: Renamed the Donation form block

= 0.4.0: June 15th, 2023 =
* NOTE: This is another beta release with breaking changes to the form builder. For the best experience, please delete any existing v3 forms and start fresh.
* New: Added multi-step form design
* New: Added per-form email settings
* New: Added login and registration block and settings
* New: Added new hooks and apis for form extensions
* Refactor: use @wordpress/scripts to load all form builder dependencies from WordPress
* Update: require minimum versions for GiveWP 2.29.0 and WordPress 5.9
* Fix: donation amount block's order of recurring period options
* Fix: omit redundant name and email fields from Stripe Payment Element
* Fix: various form builder UI fixes and bugs

= 0.3.3: May 4th, 2023 =
* Enhancement: The GiveWP Jedi high council is hosting a beta focus group on May 11th 2023 to provide focused feedback about our new visual donation form builder.  We updated the welcome banner to announce this.
* Enhancement: Updated donation form compatibility with GiveWP 2.27.0 gateway api updates
* New: Added notice for recurring gateways in the form builder
* Fix: Improvements to custom styles modal
* Fix: Amount field now uses label attribute setting
* Fix: Resolved issue with adding empty sections in the form builder
* Fix: Updated subscription amount label in confirmation page & donation summary


= 0.3.2: April 12th, 2023 =
* Enhancement: Added licensing to receive future updates from givewp.com.
* Enhancement: Improved error handling when the donation form crashes.
* Fix: Prevent a crash when the form is fixed recurring and donor choice.

= 0.3.1: April 10th, 2023 =
* Enhancement: Improvements to the welcome banner content.
* Fix: Prevent a fatal error when activating without GiveWP already active.
* Fix: Prevent errors from breaking the entire donation form.

= 0.3.0: April 7th, 2023 =
* New: Added compatability with give-recurring to enable recurring donations.
* New: Added recurring donation settings to the form builder.
* New: Added recurring donation UI & processing to the donation form.
* New: Added give-recurring gateway support for Stripe, PayPal Standard, and PayPal Donations.
* New: Added visual tour to the form builder.
* New: Added support for draft donations forms.

= 0.2.0: Feb 28th, 2023 =
* New: Added amount field settings for fixed, multi-level, and custom amounts.
* Update: Updated classic form design to use the new amount field settings.
* New: Added base styles for custom form designs.
* New: Updated form builder UI to use default WP colors instead of GiveWP branding colors.
* Fix: Fixed issue with receipt not displaying donor meta.

= 0.1.0: Jan 30th, 2023 =
* New: Initial internal release!
* New: Added visual donation form builder.
* New: Added custom text fields to the form builder.
* New: Added donation form that renders from the new form builder.
* New: Added form design / template API to the new donation form.
* New: Added form design tab and settings to form builder for design customization and live previews of the form.
* New: Added Donation Goal settings to the form builder.
* New: Added Receipt Heading and Description settings to the form builder.
* New: Added backwards-compatability for single donation form pages.
* New: Added URL settings for single donation form pages.
* New: Added auto-validation for the new donation form processing.
* New: Added new donation form gutenberg block for displaying the form on any page.
* New: Added stateless confirmation receipts to the new donation form processing with support for custom fields, fee recovery, and subscriptions.
* New: Added new Stripe gateway using the new Stripe Payment Element API.
* New: Added compatability for PayPal Standard gateway.
