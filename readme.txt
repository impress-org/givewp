=== Give - WordPress Donation Plugin ===
Contributors: wordimpress, dlocc, webdevmattcrom
Donate link: http://givewp.com/
Tags: donation, donations, donation plugin, wordpress donation plugin, wp donation, ecommerce, e-commerce, fundraising, fundraiser, crowdfunding, wordpress donations, commerce, wordpress ecommerce, giving, charity, donate, gifts, non-profit, paypal, stripe, churches, nonprofit, paypal donations, paypal donate, stripe donations, stripe donate, authorize.net, authorize.net donations
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: 0.9.5.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Democratizing Generosity - Empower your cause: start accepting donations on WordPress with Give.

== Description ==

**[Give's](https://givewp.com "Visit the the Give website")** goal is to "Democratize Generosity". Give is the most robust WordPress plugin available for accepting donations. Upon activation, Give provides you with powerful features  towards helping your raise more funds for your cause.

[youtube https://www.youtube.com/watch?v=gNdEelhjoKE]

= Features Include: =

* Goal tracking per form
* Simple and pain free giving
* Zero commission charges
* Own all your donation data
* Accept Single, Custom, and Multi-level donations
* Easy to customize and enhance
* Robust reporting features
* Customizable emails
* PayPal Standard and Offline Payments
* Custom user roles
* Dedicated support and development
* ... and much more!

Find out more on the **[Give website](https://givewp.com "Visit the the Give website")**.

= Why Give? =

Prior to Give there was no single go-to solution for accepting donations on WordPress. Sure, Gravity Forms and WooCommerce are great plugins but they're not developed to work specifically with donations. This can often lead to your users being confused by unnecessary cart systems, incorrect terminology, or lack of giving flexibility. As an admin, you may have experience wrestling with the various other WordPress donation plugins. Dealing with the lack of documentation and support can be a real pain. There's a better way. Now you have Give.

= Simple and Pain Free Giving =

Give works great right in WordPress in a way that you're already familiar in working. Simply install and activate the plugin to get started. The backbone of Give lies within its forms. Forms are what allow you to accept donations anywhere on your website. When creating a form you may specify whether the form is a single or multi-level amount. As well, forms can accept a user provided donation amount.

= First Time Users =

For new users, we suggest reviewing our [documentation](https://givewp.com/documentation "Visit the Give docs") to get an understanding of how the plugin works prior to using it. If you run into any trouble, [support](https://givewp.com/support "Visit the Give support page") is here to help you with your issues, questions, and concerns.

= Zero Commission Charges =

We earn money by selling add-ons. The money you raise using Give is yours to go to support your cause. Period. Add-ons are premium features that enable you to extend the functionality of Give. For example, you use one of our add-ons to accept funds through your favorite payment gateway.

= Easy to Customize and Enhance =

Give is built by [WordImpress](http://wordimpress.com "Visit the the developers of Give - WordImpress"), our plugins are built from the ground up to be intuitive and easy to use. Along with the plugin is a support team that is dedicated, fast to respond, and always willing to squash bugs and help troubleshoot.

= Connect with Give =

Stay in touch with us for important plugin news and updates:

* **[Give Website](https://givewp.com "Visit the the Give website")**
* **[Newsletter](http://eepurl.com/bggG99 "Subscribe via MailChimp")**
* **[Facebook](http://facebook.com/wpgive "Visit the Give on Facebook")**
* **[GitHub](https://github.com/WordImpress/Give "Visit the the developers of Give - WordImpress")**
* **[WordImpress](http://wordimpress.com "Visit the the developers of Give - WordImpress")**

= Contribute to Give =

This plugin is open source and we're always looking for more contributors. Whether you know another language, can code like no ones business, or just have an idea, we would love your help and input. To contribute to Give please head over to our website or view/fork/watch the GitHub repository to learn more about what issues we're tackling and about the project.

= A Tribute to Open Source =

*"Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open source software is made by many people, and distributed under licenses that comply with the open source Definition."*

**~ The Open Source Initiative**

Give is a tribute to the spirit and philosophy of open source. We at WordImpress gladly embrace the open source philosophy both in how Give itself was developed, and how we hope to see others build more from our code base.

Give would not have been possible without the tireless efforts of these open source projects and their talented developers:

* Pippin Williamson and his wonderful development team, Easy Digital Downloads
* Mike Jolley and the whole WooThemes Team, WooCommerce
* Carl Hancock and his entire crew, Gravity Forms
* Joost De Valk and the Yoast team, WordPress SEO
* Justin Sternberg and the whole WebDevStudios team, CMB2

== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0.15 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click "Add New".

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= How is Give better than *Gravity Forms* for accepting donations through WordPress? =

First off, we love Gravity Forms. It's a great plugin and has a lot of good features for accepting donations. That being said, it lacks substantial donation reporting features and requires a developer license ($199/yr) to accept credit card payments on site.

= How is Give better than *WooCommerce* for accepting donations on WordPress? =

We also really like WooCommerce. It's hands-down the most robust eCommerce platform for WordPress. But that's also the issue. Typically you don't need a cart system, shipping, or tax calculations to accept donations. On top of that, customizing the plugin's terminology is a daunting task. You never want your users to receive an "invoice" for the donation "product" they "purchased". Rather, you'd like for them to receive a receipt for the donation they gave. WooCommerce can do donations, but that's not what it was designed for.

== Screenshots ==

1. The donation fields as a responsive modal pop-up

2. A sample stylized Give form

3. Bar graph charting

4. Multi-level donations

== Upgrade Notice ==

Version 0.9.5 beta is still in beta, so if you find any bugs or issues please let us know! This release adds Goal Tracking and several other important updates.

== Changelog ==

= 0.9.5.1 beta =
* Fix: Incorrect usage of edd_get_option rather than give_get_option in recent PayPal Standard updates

= 0.9.5 beta =
* New: [give_profile_editor] shortcode that enables donors to customize their account information on the frontend #130 https://github.com/WordImpress/Give/issues/130
* New: Uninstall.php file which deletes ALL data if the user chooses to do so under Settings > Advanced
* New: composer.json file for developers
* New: Dynamic sidebar for singular Give Donation Forms. The sidebar will appear under Appearances > Widgets if you have not disabled Give's singular post type in Give > Settings > Display Options. You can add widgets of your choosing to this section and they will display to the left of your forms, below the main form featured image.
* New: Offline Donation enhancements including customizable donation instructions email sent to user upon form completion. See: https://github.com/WordImpress/Give/issues/124
* New: Goals for Donation Forms. Thanks @ibndawood https://github.com/ibndawood @see https://github.com/WordImpress/Give/issues/42
* New: Admin CSS improvement - Now conditional fields are indicated with a slight gray background color
* New: Script Optimization - Give now only loads one minified JS script and one CSS file to keep load times fast and minimize footprint
* New: Using Grunt to generate POT file now for much more timely and accurate translations
* New: Give now has Composer support @see: https://packagist.org/packages/wordimpress/give thanks @michaelbeil
* Fix: Admin Logs CSS: https://github.com/WordImpress/Give/issues/127
* Fix: Incorrect amount formatting when currency separators set to "," for both thousands and decimals. @see: https://github.com/WordImpress/Give/issues/150
* Fix: Broken "lock" image that appears above donation fields for SSL sites @see: https://github.com/WordImpress/Give/issues/128
* Fix: Updated Magnific class to prevent conflicts with other Magnific modals

= 0.9 beta =
* New: Global options to disable the form excerpt and featured image found under Give > Settings > Display Options
* New: Enable the billing details section for offline donations. The fieldset will appear above the offline donation instructions. Note: You may customize this option per form as needed. Per request https://github.com/WordImpress/Give/issues/26
* New: Theme template compatibility updates for Flatsome, X Theme, and Avada, Twenty-*
* New: Two new filters introduced give_default_wrapper_start and give_default_wrapper_end to modify template wrappers
* New: PayPal Standard: Allow Option to Switch from Donations to Standard transactions https://github.com/WordImpress/Give/issues/121
* New: Brazilian Portuguese Translation https://github.com/WordImpress/Give/issues/107
* Update: CMB2 updated to latest
* Update: CMB2 moved directories
* Fix: Admin Multi-Level Amount Fields Not Passed through give_format_amount() https://github.com/WordImpress/Give/issues/65
* Fix: Welcome screen CSS issue with WP 4.2+ https://github.com/WordImpress/Give/issues/119
* Fix: Admin Form Creation: Custom Amount Text Field Won't Accept Empty Value https://github.com/WordImpress/Give/issues/72
* Fix: Dollar sign in modal doesn't always work https://github.com/WordImpress/Give/issues/120
* Fix: Reports > Logs > Payment Errors > View Transaction Details now opens properly in Thickbox modal - we weren't properly enqueuing thickbox styles and script in wp-admin
* For complete details of release milestone please visit: https://github.com/WordImpress/Give/issues?q=milestone%3A%220.9+Release%22

= 0.8.6 beta =
* New: Option to "Disable Welcome Screen" added to Settings > Display Options for those users to set if they don't want a welcome screen appearing
* Fix: Updated install process to fix #114 - https://github.com/WordImpress/Give/issues/114 - Thanks @paaljoachim
* Fix: Permissions bug with edit_give_payments within admin/payments/actions.php and within the class-give-roles.php preventing admins of new installs from deleting donations - Thanks @jakestpeter

= 0.8.5 beta =
* Fix: Global vs Form Payment Gateways https://github.com/WordImpress/Give/issues/86
* Fix: Setting Section Title Not Displaying Proper Text https://github.com/WordImpress/Give/issues/87
* Fix: Prefixed "icon" and "icon-question" classes to mitigate conflicts: https://github.com/WordImpress/Give/issues/103
* Fix: {name} isn't correctly rendered in test email: https://github.com/WordImpress/Give/issues/100 - Thanks @sumobi
* Fix: When exporting a report, apostrophe's are not correctly shown: https://github.com/WordImpress/Give/issues/96 - Thanks @sumobi
* Fix: PHP warning when exporting PDF: https://github.com/WordImpress/Give/issues/93 - Thanks @sumobi
* Fix: Property of non-object on Forms Report: https://github.com/WordImpress/Give/issues/91 - Thanks @pippinsplugins
* Fix: PHP Notice: Undefined variable: unlimited: https://github.com/WordImpress/Give/issues/89 - Thanks @sumobi
* Fix: Prefix .icon class to prevent conflicts #103: https://github.com/WordImpress/Give/issues/103 - Thanks @stevengliebe
* Update: Removed unnecessary contextual help files until we decide how we are going to approach this with the plugin
* Update: Inline code comments improved to be more specific to Give - some were incorrectly describing old EDD functionality
* Security: Hardened URLs with esc_url() across the plugin core

= 0.8 beta =

* Initial plugin release. Yippee!