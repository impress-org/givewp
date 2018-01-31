# Change Log

## [2.0.2](https://github.com/WordImpress/Give/tree/2.0.2) (2018-01-31)
[Full Changelog](https://github.com/WordImpress/Give/compare/2.0.1...2.0.2)

**Implemented enhancements:**

- Add `Give\_Cache` option to disable cache from wp-config.php [\#2735](https://github.com/WordImpress/Give/issues/2735)

**Fixed bugs:**

- WP\_Query being modified in upgrade routine by Polylang plugin causing issues [\#2747](https://github.com/WordImpress/Give/issues/2747)
- Getting unexpected error message while using tools [\#2744](https://github.com/WordImpress/Give/issues/2744)
- Use dynamic cache group name while deleting cache [\#2743](https://github.com/WordImpress/Give/issues/2743)
- Prevent importer modifying existing form settings [\#2737](https://github.com/WordImpress/Give/issues/2737)
- Shortcode generator issue with inserting Donation Form goal [\#2728](https://github.com/WordImpress/Give/issues/2728)
- Import doesn't import Donations or Donors in version 2.0.1 [\#2725](https://github.com/WordImpress/Give/issues/2725)
- Form sales does not update when change donation status {complete} --\> {abandon} [\#2708](https://github.com/WordImpress/Give/issues/2708)
- Quick Enable/Disable of new Email API not working properly [\#2649](https://github.com/WordImpress/Give/issues/2649)
- When viewing donations filtered by donor performing bulk actions causes to lose filter [\#2334](https://github.com/WordImpress/Give/issues/2334)
- The donation form should reset when go back from the donation confirmation page. [\#2091](https://github.com/WordImpress/Give/issues/2091)

**Closed issues:**

- Styling issue for addon list on update page [\#2706](https://github.com/WordImpress/Give/issues/2706)
- Remove licences tab if there is no add-on activated   [\#2375](https://github.com/WordImpress/Give/issues/2375)
- Improve donor detail page heading [\#2286](https://github.com/WordImpress/Give/issues/2286)
- Minimum and maximum donation amount slider [\#1920](https://github.com/WordImpress/Give/issues/1920)
- Clarify language on the Login fields. [\#2745](https://github.com/WordImpress/Give/issues/2745)
- Consider having ajax issue admin notice link open in new tab/window [\#2727](https://github.com/WordImpress/Give/issues/2727)
- Deprecating the second $\_POST parameter [\#2663](https://github.com/WordImpress/Give/issues/2663)

**Merged pull requests:**

- Release/2.0.2 [\#2753](https://github.com/WordImpress/Give/pull/2753) ([DevinWalker](https://github.com/DevinWalker))
- Do not update meta when form is already being created \#2737 [\#2752](https://github.com/WordImpress/Give/pull/2752) ([raftaar1191](https://github.com/raftaar1191))
- Revert "Add/Update Donation goal meta on donation form update or when donating \#2250" [\#2750](https://github.com/WordImpress/Give/pull/2750) ([ravinderk](https://github.com/ravinderk))
- Issues 2744 [\#2749](https://github.com/WordImpress/Give/pull/2749) ([raftaar1191](https://github.com/raftaar1191))
- clarify language on login form [\#2746](https://github.com/WordImpress/Give/pull/2746) ([Benunc](https://github.com/Benunc))
- Issue \#2091 - Reset Form Fields on clicking browser back button [\#2742](https://github.com/WordImpress/Give/pull/2742) ([mehul0810](https://github.com/mehul0810))
- Donation form preview fixes \#2456 [\#2741](https://github.com/WordImpress/Give/pull/2741) ([PareshRadadiya](https://github.com/PareshRadadiya))
- Issue \#2708 - Form Sales and Amount doesn't update on donation status change [\#2736](https://github.com/WordImpress/Give/pull/2736) ([mehul0810](https://github.com/mehul0810))
- Fix Status issues when importing donation \#2725 [\#2733](https://github.com/WordImpress/Give/pull/2733) ([raftaar1191](https://github.com/raftaar1191))
- Change dashboard donor single page title [\#2732](https://github.com/WordImpress/Give/pull/2732) ([raftaar1191](https://github.com/raftaar1191))
- Improve React / JSX linting and Form Preview [\#2731](https://github.com/WordImpress/Give/pull/2731) ([PareshRadadiya](https://github.com/PareshRadadiya))
- Issue/2728 - Resolve query issue with Donation Form Goal option in shortcode builder [\#2729](https://github.com/WordImpress/Give/pull/2729) ([DevinWalker](https://github.com/DevinWalker))
- Made PR \#2724 compatible with feature/gutenberg-donation-form-block/ branch [\#2726](https://github.com/WordImpress/Give/pull/2726) ([PareshRadadiya](https://github.com/PareshRadadiya))
- Fix changelog [\#2723](https://github.com/WordImpress/Give/pull/2723) ([mathetos](https://github.com/mathetos))
- update and clean up more phpdoc params [\#2722](https://github.com/WordImpress/Give/pull/2722) ([tw2113](https://github.com/tw2113))
- Fix style issues in Admin plugin update page \#2706 [\#2713](https://github.com/WordImpress/Give/pull/2713) ([raftaar1191](https://github.com/raftaar1191))
- Add/Update Donation goal meta on donation form update or when donating \#2250 [\#2691](https://github.com/WordImpress/Give/pull/2691) ([raftaar1191](https://github.com/raftaar1191))
- Remove Parameter the unwanted parameter \#2663 [\#2687](https://github.com/WordImpress/Give/pull/2687) ([raftaar1191](https://github.com/raftaar1191))
- Add Donor filter that was getting lost when changing the donation status \#2334 [\#2686](https://github.com/WordImpress/Give/pull/2686) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1920 - Minimum and maximum donation amount slider [\#2666](https://github.com/WordImpress/Give/pull/2666) ([emgk](https://github.com/emgk))

## [2.0.1](https://github.com/WordImpress/Give/tree/2.0.1) (2018-01-25)
[Full Changelog](https://github.com/WordImpress/Give/compare/2.0.0...2.0.1)

**Fixed bugs:**

- {billing\_address} showing up blank on PDF Receipts [\#2707](https://github.com/WordImpress/Give/issues/2707)
- Resolve failing unit tests in 2.0.1 [\#2702](https://github.com/WordImpress/Give/issues/2702)
- View all donation of donor link is not working [\#2683](https://github.com/WordImpress/Give/issues/2683)
- \[Give 2.0\] MySQL error after updating on Email Preview [\#2677](https://github.com/WordImpress/Give/issues/2677)
- Custom Payment meta not being stored in 2.0+ [\#2675](https://github.com/WordImpress/Give/issues/2675)
- Fix stalled upgrade process [\#2671](https://github.com/WordImpress/Give/issues/2671)
- Database error on donations list page in backend in Windows 64-bit OS. [\#2533](https://github.com/WordImpress/Give/issues/2533)
- Getting Notice in donation-history Page [\#2326](https://github.com/WordImpress/Give/issues/2326)

**Closed issues:**

- Don't use price fallback for amounts because often it's confusing and redundant [\#2700](https://github.com/WordImpress/Give/issues/2700)
- Remove unwanted code [\#2698](https://github.com/WordImpress/Give/issues/2698)
- Add setting in Form Terms and Condition that it should be checked by default or not [\#2685](https://github.com/WordImpress/Give/issues/2685)
- Removing the Message when give Add-on is activating in the plugin section with Ajax [\#2681](https://github.com/WordImpress/Give/issues/2681)
- \[Give 2.0\] New user notification emails missing headings [\#2670](https://github.com/WordImpress/Give/issues/2670)
- Add a filter/option for separate email templates for donor receipts and admin notifications [\#1493](https://github.com/WordImpress/Give/issues/1493)
- Upgrade Process Stalled at 0% under Privacy Mode [\#2697](https://github.com/WordImpress/Give/issues/2697)
- Update PopUp message for Restart and Pause button of upgrade routine [\#2696](https://github.com/WordImpress/Give/issues/2696)
- Give 2.0.1 Testing Checklist [\#2695](https://github.com/WordImpress/Give/issues/2695)
- Add clarity to the Settings Importer inline docs [\#2688](https://github.com/WordImpress/Give/issues/2688)
- Add icon for per form emails tab and update tab title [\#2680](https://github.com/WordImpress/Give/issues/2680)
- Add table prefix in system report [\#2678](https://github.com/WordImpress/Give/issues/2678)
- Add pause and restart feature to background update [\#2676](https://github.com/WordImpress/Give/issues/2676)
- Currency Position dosn't update when you change Currency \(you have to save\) [\#2667](https://github.com/WordImpress/Give/issues/2667)

**Merged pull requests:**

- Fix - Displays Upgrade Notice on Fresh Installs [\#2714](https://github.com/WordImpress/Give/pull/2714) ([mehul0810](https://github.com/mehul0810))
- Release/2.0.1 [\#2712](https://github.com/WordImpress/Give/pull/2712) ([DevinWalker](https://github.com/DevinWalker))
- Issue \#2692 - Fix Table Creation Issue when utf8mb4 [\#2705](https://github.com/WordImpress/Give/pull/2705) ([mehul0810](https://github.com/mehul0810))
- Issue/2700 - Don't use fallback in give\_get\_price\_option\_name\(\) to simplify UX [\#2701](https://github.com/WordImpress/Give/pull/2701) ([DevinWalker](https://github.com/DevinWalker))
- Phpdoc cleanup to help with Scrutinizer in \#675 [\#2694](https://github.com/WordImpress/Give/pull/2694) ([tw2113](https://github.com/tw2113))
- rename "customer\_id" variables to a better fit of "donor\_id" [\#2693](https://github.com/WordImpress/Give/pull/2693) ([tw2113](https://github.com/tw2113))
- Remove variable that is globally accessible \#2663 [\#2690](https://github.com/WordImpress/Give/pull/2690) ([raftaar1191](https://github.com/raftaar1191))
- Issue \#2688 — Add clarity to settings Importer docs [\#2689](https://github.com/WordImpress/Give/pull/2689) ([Benunc](https://github.com/Benunc))
- Issue/2677|2533 - MySQL error after updating on Email Preview [\#2679](https://github.com/WordImpress/Give/pull/2679) ([emgk](https://github.com/emgk))
- issues \#2667 On currency change the currency place text symbol \#2667 [\#2674](https://github.com/WordImpress/Give/pull/2674) ([raftaar1191](https://github.com/raftaar1191))

## [2.0.0](https://github.com/WordImpress/Give/tree/2.0.0) (2018-01-17)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.19...2.0.0)

**Implemented enhancements:**

- Hard to map a field to correct column on large screen.  [\#2507](https://github.com/WordImpress/Give/issues/2507)

**Fixed bugs:**

- Remove unwanted hidden fields from Donation Process Page [\#2664](https://github.com/WordImpress/Give/issues/2664)
- ID not define proper when guess donation is disable [\#2661](https://github.com/WordImpress/Give/issues/2661)
- \[Give 2.0\] Show 'Undefined' while Run Background Process. [\#2654](https://github.com/WordImpress/Give/issues/2654)
- Not redirecting to Welcome page on Plugin Activation 2.0 [\#2650](https://github.com/WordImpress/Give/issues/2650)
- Getting PHP Notice at the Time of activating the plugin on a fresh Sites [\#2637](https://github.com/WordImpress/Give/issues/2637)
- \[Give 2.0\] Adding additional parameter with ID returns error in /donations/ endpoint [\#2633](https://github.com/WordImpress/Give/issues/2633)
- \[Give 2.0\] Email recipient setting missing from form settings for email notification [\#2628](https://github.com/WordImpress/Give/issues/2628)
- \[give\_login\] not redirecting properly [\#2622](https://github.com/WordImpress/Give/issues/2622)
- \[Give 2.0\] Recipients shows blank space when add blank value in Add recipients [\#2619](https://github.com/WordImpress/Give/issues/2619)
- \[Give 2.0\] Upgrade routine stuck at 0% [\#2610](https://github.com/WordImpress/Give/issues/2610)
- \[Give 2.0\] Donation Goal - Number of donations option does not work as intended always [\#2607](https://github.com/WordImpress/Give/issues/2607)
- \[Give 2.0\] Email send on page refresh [\#2606](https://github.com/WordImpress/Give/issues/2606)
- \[Give 2.0\] preview\_id and user\_id repeated in query string when preview email [\#2596](https://github.com/WordImpress/Give/issues/2596)
- \[Give 2.0\] API Requests Logs are not working properly [\#2592](https://github.com/WordImpress/Give/issues/2592)
- \[Give 2.0\] PHP log notices [\#2584](https://github.com/WordImpress/Give/issues/2584)
- \[Give 2.0\] WordPress Default Admin Email Goes Blank [\#2581](https://github.com/WordImpress/Give/issues/2581)
- Default payment method is not selected by default if we deactivate any default payment gateway add-on. [\#2570](https://github.com/WordImpress/Give/issues/2570)
- \[Give 2.0\] Renewals Display Incorrect Level on Donations Listing Screen [\#2567](https://github.com/WordImpress/Give/issues/2567)
- \[Give 2.0\] Timeouts on Donations Listing Page with Caching Disabled [\#2566](https://github.com/WordImpress/Give/issues/2566)
- \[Give 2.0\] Billing address State/County Placeholder  value ''undefined' [\#2565](https://github.com/WordImpress/Give/issues/2565)
- Donations get processed with Empty First Name [\#2562](https://github.com/WordImpress/Give/issues/2562)
- \[Give 2.0\] Give\_Payments\_Query\(\) and 'meta\_key' arg [\#2561](https://github.com/WordImpress/Give/issues/2561)
- \[Give 2.0\] Upgrade WP Db Error [\#2560](https://github.com/WordImpress/Give/issues/2560)
- Income Reports not showing Total Income with any filter [\#2472](https://github.com/WordImpress/Give/issues/2472)
- Active floated label inputs inherit background color of container [\#2155](https://github.com/WordImpress/Give/issues/2155)
- Address fields are not being passed to the API [\#1112](https://github.com/WordImpress/Give/issues/1112)

**Closed issues:**

- Add email option to form setting also [\#2659](https://github.com/WordImpress/Give/issues/2659)
- \[Give 2.0\] Enabling/Disabling the "Email access" email should be restricted by the Email Access setting [\#2658](https://github.com/WordImpress/Give/issues/2658)
- More inline doc fixes [\#2647](https://github.com/WordImpress/Give/issues/2647)
- \[Give 2.0\] Donations are not processing with latest code [\#2635](https://github.com/WordImpress/Give/issues/2635)
- \[Give 2.0\] Incorrect message displayed when removing email recipient [\#2634](https://github.com/WordImpress/Give/issues/2634)
- Constants need to be obeyed [\#2631](https://github.com/WordImpress/Give/issues/2631)
- \[Give 2.0\] Uncaught TypeError when switch payment gateway in front-end. [\#2630](https://github.com/WordImpress/Give/issues/2630)
- \[Give 2.0\] Inline Documentation is confusing on the emails settings. [\#2626](https://github.com/WordImpress/Give/issues/2626)
- \[Give 2.0\] Snippet Library Snippet causing fatal error on 2.0 [\#2625](https://github.com/WordImpress/Give/issues/2625)
- \[Give 2.0\] Grammar and clarity needed for new email settings in-app docs. [\#2620](https://github.com/WordImpress/Give/issues/2620)
- New donations logs are not display if user does not update the DB in 2.0 [\#2618](https://github.com/WordImpress/Give/issues/2618)
- \[Give 2.0\] Exporting PDF of Donations and Income gives TCPDF error [\#2614](https://github.com/WordImpress/Give/issues/2614)
- \[Give 2.0\] ToolTips affected the donation listing view [\#2612](https://github.com/WordImpress/Give/issues/2612)
- Transaction ID is displayed as Donation ID under Donations Detail Page [\#2604](https://github.com/WordImpress/Give/issues/2604)
- images not showing in pdf and email receipts [\#2601](https://github.com/WordImpress/Give/issues/2601)
- \[Give 2.0\] Separator between the links on donor detail page is missing [\#2599](https://github.com/WordImpress/Give/issues/2599)
- \[Give 2.0\] give\_get\_meta and related meta functions should support all the custom tables [\#2593](https://github.com/WordImpress/Give/issues/2593)
- \[Give 2.0\] Settings Tabs are not responsive. [\#2588](https://github.com/WordImpress/Give/issues/2588)
- Menu issues in Donation form single page of dashboard in Email notification section  [\#2587](https://github.com/WordImpress/Give/issues/2587)
- Admin should able to restart the upgrade if it's get fail  [\#2585](https://github.com/WordImpress/Give/issues/2585)
- Goal Column under Donation Forms Listing is showing incorrect amount  [\#2582](https://github.com/WordImpress/Give/issues/2582)
- Disable Payment Gateway Settings when Payment Gateways are disabled. [\#2576](https://github.com/WordImpress/Give/issues/2576)
- Donation form should have nonce verification. [\#2568](https://github.com/WordImpress/Give/issues/2568)
- Getting PHP notices when passing meta query in Give\_Payments\_Query [\#2556](https://github.com/WordImpress/Give/issues/2556)
- Run Database upgrade in background. [\#2531](https://github.com/WordImpress/Give/issues/2531)
- Simplify give\_is\_admin\_page conditional [\#1091](https://github.com/WordImpress/Give/issues/1091)

**Merged pull requests:**

- Issues 2661 new [\#2669](https://github.com/WordImpress/Give/pull/2669) ([raftaar1191](https://github.com/raftaar1191))
- Release/2.0 [\#2668](https://github.com/WordImpress/Give/pull/2668) ([DevinWalker](https://github.com/DevinWalker))
- Add class give-disable which return false and add some css  and run g… [\#2665](https://github.com/WordImpress/Give/pull/2665) ([raftaar1191](https://github.com/raftaar1191))
- Fix element id name \#2661 [\#2662](https://github.com/WordImpress/Give/pull/2662) ([raftaar1191](https://github.com/raftaar1191))
- \[Feature\] \#2659: Add email options to form setting [\#2660](https://github.com/WordImpress/Give/pull/2660) ([ravinderk](https://github.com/ravinderk))
- grammar/clarity cleanup. [\#2648](https://github.com/WordImpress/Give/pull/2648) ([Benunc](https://github.com/Benunc))
- Issue \#2507 Fix Hard to map a field to correct column on large screen [\#2645](https://github.com/WordImpress/Give/pull/2645) ([akashsonic](https://github.com/akashsonic))
- Issue/1091 Fix Simplify give\_is\_admin\_page conditional [\#2639](https://github.com/WordImpress/Give/pull/2639) ([emgk](https://github.com/emgk))
- Issue/2635 [\#2636](https://github.com/WordImpress/Give/pull/2636) ([mehul0810](https://github.com/mehul0810))
- Issue/2631 [\#2632](https://github.com/WordImpress/Give/pull/2632) ([mehul0810](https://github.com/mehul0810))
- Issues/2619 [\#2629](https://github.com/WordImpress/Give/pull/2629) ([ravinderk](https://github.com/ravinderk))
- Issues/2610 [\#2627](https://github.com/WordImpress/Give/pull/2627) ([ravinderk](https://github.com/ravinderk))
- Issues 2069 [\#2624](https://github.com/WordImpress/Give/pull/2624) ([raftaar1191](https://github.com/raftaar1191))
- Properly allow `login-redirect` attribute to override variable \#2622 [\#2623](https://github.com/WordImpress/Give/pull/2623) ([DevinWalker](https://github.com/DevinWalker))
- Grammar and clarity for the inline docs for the new email API settings. [\#2621](https://github.com/WordImpress/Give/pull/2621) ([Benunc](https://github.com/Benunc))
- Issue/2614 [\#2616](https://github.com/WordImpress/Give/pull/2616) ([mehul0810](https://github.com/mehul0810))
- issue/2610 [\#2615](https://github.com/WordImpress/Give/pull/2615) ([ravinderk](https://github.com/ravinderk))
- Issue/2612 [\#2613](https://github.com/WordImpress/Give/pull/2613) ([mehul0810](https://github.com/mehul0810))
- Fix: Issue \#2606 [\#2609](https://github.com/WordImpress/Give/pull/2609) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/2607 [\#2608](https://github.com/WordImpress/Give/pull/2608) ([mehul0810](https://github.com/mehul0810))
- issue/1112 [\#2603](https://github.com/WordImpress/Give/pull/2603) ([emgk](https://github.com/emgk))
- Issue/2599 [\#2600](https://github.com/WordImpress/Give/pull/2600) ([mehul0810](https://github.com/mehul0810))
- Fix: preview\_id and user\_id repeated in query string when preview email \#2596 [\#2598](https://github.com/WordImpress/Give/pull/2598) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/2155 [\#2597](https://github.com/WordImpress/Give/pull/2597) ([emgk](https://github.com/emgk))
- Issue/2592 [\#2594](https://github.com/WordImpress/Give/pull/2594) ([mehul0810](https://github.com/mehul0810))
- \#2581  \[Give 2.0\] WordPress Default Admin Email Goes Blank [\#2590](https://github.com/WordImpress/Give/pull/2590) ([akashsonic](https://github.com/akashsonic))
- Issue/2588 [\#2589](https://github.com/WordImpress/Give/pull/2589) ([mehul0810](https://github.com/mehul0810))
- Issue/2582 [\#2583](https://github.com/WordImpress/Give/pull/2583) ([mehul0810](https://github.com/mehul0810))
- Issue/2568 [\#2573](https://github.com/WordImpress/Give/pull/2573) ([emgk](https://github.com/emgk))
- Issue/2570 [\#2571](https://github.com/WordImpress/Give/pull/2571) ([mehul0810](https://github.com/mehul0810))
- Issue/2567 [\#2569](https://github.com/WordImpress/Give/pull/2569) ([mehul0810](https://github.com/mehul0810))
- Issue/2562 [\#2564](https://github.com/WordImpress/Give/pull/2564) ([mehul0810](https://github.com/mehul0810))
- Issues/2561 [\#2563](https://github.com/WordImpress/Give/pull/2563) ([ravinderk](https://github.com/ravinderk))
- Add variable check \#2556 [\#2557](https://github.com/WordImpress/Give/pull/2557) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2531 [\#2541](https://github.com/WordImpress/Give/pull/2541) ([ravinderk](https://github.com/ravinderk))
- Issue/2472 [\#2529](https://github.com/WordImpress/Give/pull/2529) ([mehul0810](https://github.com/mehul0810))

## [1.8.19](https://github.com/WordImpress/Give/tree/1.8.19) (2017-12-21)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.18.18...1.8.19)

**Fixed bugs:**

- \[Give 1.8.18\] Getting Database error when verify the email. [\#2554](https://github.com/WordImpress/Give/issues/2554)

**Merged pull requests:**

- Give recurring issues 424 [\#2559](https://github.com/WordImpress/Give/pull/2559) ([raftaar1191](https://github.com/raftaar1191))
- Release/1.8.19 [\#2558](https://github.com/WordImpress/Give/pull/2558) ([DevinWalker](https://github.com/DevinWalker))
- Issue/2554 [\#2555](https://github.com/WordImpress/Give/pull/2555) ([mehul0810](https://github.com/mehul0810))

## [1.18.18](https://github.com/WordImpress/Give/tree/1.18.18) (2017-12-20)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.17...1.18.18)

**Implemented enhancements:**

- \[Give 2.0\] Show emails status in system report [\#2550](https://github.com/WordImpress/Give/issues/2550)
- \[Give 2.0\] Merge Revamped Email Access Functionality of 1.8.17 [\#2504](https://github.com/WordImpress/Give/issues/2504)

**Fixed bugs:**

- Bug with shortcode inserter, creating new pages/posts and the enter key [\#2545](https://github.com/WordImpress/Give/issues/2545)
- \[Give 2.0\] PHP Warning: Division by zero display when set Number of Donations in Donation Goal [\#2534](https://github.com/WordImpress/Give/issues/2534)
- Minimum amount incorrect with changed decimal and thousands separators. [\#2526](https://github.com/WordImpress/Give/issues/2526)
- Give PDF Report not output variable price. [\#2523](https://github.com/WordImpress/Give/issues/2523)
- \[Give 2.0\] Emails sent twice when sent via Send Test Email under Settings \> Emails [\#2510](https://github.com/WordImpress/Give/issues/2510)
- \[Give 2.0\] Not getting donor's meta from payment object when Give caching is enabled. [\#2506](https://github.com/WordImpress/Give/issues/2506)
- \[Give 2.0\] Can't preview or send test notification email from form edit page in backend. [\#2498](https://github.com/WordImpress/Give/issues/2498)
- Error Log receives messages WordPress database error Duplicate column [\#2493](https://github.com/WordImpress/Give/issues/2493)
- Donation total not showing up when switch levels if donation form has default donation level with $0 amount. [\#2488](https://github.com/WordImpress/Give/issues/2488)
- Donor delete button doesn't work when select donor\(s\) on donor listing page. [\#2487](https://github.com/WordImpress/Give/issues/2487)
- Unformatted amount showing in Donation Confirmation page. [\#2484](https://github.com/WordImpress/Give/issues/2484)
- Data Tool: Reset dropdown value  [\#2475](https://github.com/WordImpress/Give/issues/2475)
- \[Give 2.0\] Donor's information removed from donation after upgrading database [\#2464](https://github.com/WordImpress/Give/issues/2464)
- Invalid Argument Supplied when no Donation Levels [\#2451](https://github.com/WordImpress/Give/issues/2451)
- IMPORTER: Donations are being inaccurately marked as duplicates [\#2420](https://github.com/WordImpress/Give/issues/2420)
- Donor Profile Editor shortcode improvement [\#2407](https://github.com/WordImpress/Give/issues/2407)
- Donations should be easily identified for form with Set Donation Method [\#2401](https://github.com/WordImpress/Give/issues/2401)
- Plain text email template doesn't support links [\#2346](https://github.com/WordImpress/Give/issues/2346)

**Closed issues:**

- With Give activated, Purchasing and creating an account with WooCommerce forces the customer login after purchase [\#2539](https://github.com/WordImpress/Give/issues/2539)
- Hide Offline Donation Email Notification When Offline Donation is disabled [\#2538](https://github.com/WordImpress/Give/issues/2538)
- Country with no base state defined returns false instead of array\(\) [\#2528](https://github.com/WordImpress/Give/issues/2528)
- \[Give 2.0\] Preview Email field description is wrong. [\#2518](https://github.com/WordImpress/Give/issues/2518)
- \[Give 2.0\] Notice in meta\_query backward compatibility [\#2511](https://github.com/WordImpress/Give/issues/2511)
- Add custom icon to our Add-ons for the WordPress Update screen [\#2509](https://github.com/WordImpress/Give/issues/2509)
- \[Give 1.8.18\] PHP Notice for not passing second argument as Array in give\_currency\_filter fn. [\#2508](https://github.com/WordImpress/Give/issues/2508)
- Disabling "Anyone can register" doesn't stop user to create an account. [\#2499](https://github.com/WordImpress/Give/issues/2499)
- \[Give 1.8.18\] PHP Notice on donation details page in back-end. [\#2496](https://github.com/WordImpress/Give/issues/2496)
-  \[Give 2.0\] Clicking Email Notification Tab under New Donation Form goes blank [\#2489](https://github.com/WordImpress/Give/issues/2489)
- Clean Up of Legacy Code for View it in browser link under Donation Receipt Email [\#2483](https://github.com/WordImpress/Give/issues/2483)
- Give Worker still has Capabilities it Shouldn't [\#2476](https://github.com/WordImpress/Give/issues/2476)
- Add a line to System Info to indicate whether the database updates have been done or not [\#2452](https://github.com/WordImpress/Give/issues/2452)
- Improve sql query for give\_get\_purchase\_id\_by\_key fn [\#2443](https://github.com/WordImpress/Give/issues/2443)
- Add give\_ignore\_user\_abort fn [\#2442](https://github.com/WordImpress/Give/issues/2442)
- Add feature to sort donations on basis of gateway [\#2397](https://github.com/WordImpress/Give/issues/2397)
- QUESTION: Investigate how the Dashboard Widgets store data [\#2373](https://github.com/WordImpress/Give/issues/2373)
- Dashboard Update page Title [\#2353](https://github.com/WordImpress/Give/issues/2353)
- Cache direct queries and objects [\#1944](https://github.com/WordImpress/Give/issues/1944)

**Merged pull requests:**

- Release/1.8.18 [\#2551](https://github.com/WordImpress/Give/pull/2551) ([DevinWalker](https://github.com/DevinWalker))
- MinorFix/2542 [\#2549](https://github.com/WordImpress/Give/pull/2549) ([mehul0810](https://github.com/mehul0810))
- MinorFix/2545 [\#2548](https://github.com/WordImpress/Give/pull/2548) ([mehul0810](https://github.com/mehul0810))
- Issue/2545 [\#2547](https://github.com/WordImpress/Give/pull/2547) ([mehul0810](https://github.com/mehul0810))
- Issue/2539 [\#2546](https://github.com/WordImpress/Give/pull/2546) ([DevinWalker](https://github.com/DevinWalker))
- issue/2476 - Give Worker Role Cleanup [\#2544](https://github.com/WordImpress/Give/pull/2544) ([DevinWalker](https://github.com/DevinWalker))
- Filter and action add [\#2542](https://github.com/WordImpress/Give/pull/2542) ([raftaar1191](https://github.com/raftaar1191))
- Minorfix/2407 [\#2540](https://github.com/WordImpress/Give/pull/2540) ([mehul0810](https://github.com/mehul0810))
- MinorFix/2346 [\#2537](https://github.com/WordImpress/Give/pull/2537) ([mehul0810](https://github.com/mehul0810))
- Issue/2534 [\#2536](https://github.com/WordImpress/Give/pull/2536) ([mehul0810](https://github.com/mehul0810))
- Issue/2504 [\#2535](https://github.com/WordImpress/Give/pull/2535) ([mehul0810](https://github.com/mehul0810))
- Minor Fix - Failing Unit Tests [\#2532](https://github.com/WordImpress/Give/pull/2532) ([mehul0810](https://github.com/mehul0810))
- Issue/2483 - Remove legacy in browser receipt code [\#2527](https://github.com/WordImpress/Give/pull/2527) ([DevinWalker](https://github.com/DevinWalker))
- Hide first multi-level repeatable row's delete button [\#2525](https://github.com/WordImpress/Give/pull/2525) ([DevinWalker](https://github.com/DevinWalker))
- Fix: Give PDF Report not output variable price. \#2523 [\#2524](https://github.com/WordImpress/Give/pull/2524) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/2518 [\#2522](https://github.com/WordImpress/Give/pull/2522) ([mehul0810](https://github.com/mehul0810))
- Issues/2443 [\#2521](https://github.com/WordImpress/Give/pull/2521) ([ravinderk](https://github.com/ravinderk))
- Issues/2442 [\#2520](https://github.com/WordImpress/Give/pull/2520) ([ravinderk](https://github.com/ravinderk))
- Issue/2452 [\#2519](https://github.com/WordImpress/Give/pull/2519) ([mehul0810](https://github.com/mehul0810))
- Issue/2346 [\#2513](https://github.com/WordImpress/Give/pull/2513) ([mehul0810](https://github.com/mehul0810))
- Issues/2498 [\#2512](https://github.com/WordImpress/Give/pull/2512) ([ravinderk](https://github.com/ravinderk))
- Issues/2397 [\#2505](https://github.com/WordImpress/Give/pull/2505) ([ravinderk](https://github.com/ravinderk))
- Issue/2401 [\#2502](https://github.com/WordImpress/Give/pull/2502) ([mehul0810](https://github.com/mehul0810))
- Issue/2487 [\#2500](https://github.com/WordImpress/Give/pull/2500) ([mehul0810](https://github.com/mehul0810))
- Issue/2496 [\#2497](https://github.com/WordImpress/Give/pull/2497) ([emgk](https://github.com/emgk))
- Issue/2488 [\#2495](https://github.com/WordImpress/Give/pull/2495) ([emgk](https://github.com/emgk))
- Issue/2493 [\#2494](https://github.com/WordImpress/Give/pull/2494) ([mehul0810](https://github.com/mehul0810))
- Add Page Status \#2353 [\#2490](https://github.com/WordImpress/Give/pull/2490) ([raftaar1191](https://github.com/raftaar1191))
- Update drowdown on ajax completed \#2475 [\#2486](https://github.com/WordImpress/Give/pull/2486) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2420 [\#2434](https://github.com/WordImpress/Give/pull/2434) ([raftaar1191](https://github.com/raftaar1191))
- Issue/2360 [\#2379](https://github.com/WordImpress/Give/pull/2379) ([emgk](https://github.com/emgk))
- Issues/1944 [\#2374](https://github.com/WordImpress/Give/pull/2374) ([ravinderk](https://github.com/ravinderk))

## [1.8.17](https://github.com/WordImpress/Give/tree/1.8.17) (2017-12-08)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.16...1.8.17)

**Implemented enhancements:**

- Allow admins to collect donor's address [\#370](https://github.com/WordImpress/Give/issues/370)
- IMPORTER: Investigate ways to increase speed of the Importer [\#2427](https://github.com/WordImpress/Give/issues/2427)
- Use correct function to get the minimum donation amount. [\#2403](https://github.com/WordImpress/Give/issues/2403)
- Consider showing goal without decimal places [\#2372](https://github.com/WordImpress/Give/issues/2372)
- Add filter when getting payment amount  [\#2317](https://github.com/WordImpress/Give/issues/2317)
- Add helper function to get total donation amount of donor [\#2315](https://github.com/WordImpress/Give/issues/2315)
- Add filter to Give\_Payment\_Stats:get\_earnings [\#2314](https://github.com/WordImpress/Give/issues/2314)
- Add $form\_id support to give\_currency\_filter [\#2311](https://github.com/WordImpress/Give/issues/2311)
- Create WordPress environment section in Github issue and PR template [\#2310](https://github.com/WordImpress/Give/issues/2310)
- Allows to sanitize amount based on currency [\#2258](https://github.com/WordImpress/Give/issues/2258)
- Add inline notice support to Give\_Notices [\#2180](https://github.com/WordImpress/Give/issues/2180)
- User Roles and Capabilities need to be cleaned up a bit [\#2112](https://github.com/WordImpress/Give/issues/2112)
- Bulk Actions for Donors [\#2086](https://github.com/WordImpress/Give/issues/2086)
- Enforcing email access on donation history to prevent easy access through a small donation [\#2023](https://github.com/WordImpress/Give/issues/2023)
- Create setting import/export tool [\#2009](https://github.com/WordImpress/Give/issues/2009)
- Modal close button gets hidden with some themes [\#1709](https://github.com/WordImpress/Give/issues/1709)

**Fixed bugs:**

- Unable to create user when create an account checkbox is checked [\#2408](https://github.com/WordImpress/Give/issues/2408)
- Chosen field alignment issue on multiple places [\#2370](https://github.com/WordImpress/Give/issues/2370)
- Give Core 2.0 Issues updating the Database [\#2366](https://github.com/WordImpress/Give/issues/2366)
- \[Donor Profile Page\] State field is not work as in other address form field [\#2363](https://github.com/WordImpress/Give/issues/2363)
- User chosen dropdown is not visible on donor edit screen [\#2312](https://github.com/WordImpress/Give/issues/2312)
- Lockdown sensitive data throughout plugin for give\_worker role [\#1223](https://github.com/WordImpress/Give/issues/1223)
- Donation total not respect to the number of decimal [\#2461](https://github.com/WordImpress/Give/issues/2461)
- Donor name overwrite when update payment [\#2460](https://github.com/WordImpress/Give/issues/2460)
- Multi-level donation default option selected for all Level [\#2455](https://github.com/WordImpress/Give/issues/2455)
- Getting fetal error when activating the Give Plugin 2.0 [\#2446](https://github.com/WordImpress/Give/issues/2446)
- Clicking on Save Changes button under Settings should not trigger popup [\#2440](https://github.com/WordImpress/Give/issues/2440)
- Improve get\_payment\_by\_group fn [\#2432](https://github.com/WordImpress/Give/issues/2432)
- JS Error: Uncaught ReferenceError: update\_multiselect\_vals is not defined in 1.8.17 [\#2428](https://github.com/WordImpress/Give/issues/2428)
- Poupup in not opening for Modal and Button display type [\#2425](https://github.com/WordImpress/Give/issues/2425)
- Licenses not being deleted after deactivating [\#2418](https://github.com/WordImpress/Give/issues/2418)
- Typo in Documentation link on Welcome page [\#2416](https://github.com/WordImpress/Give/issues/2416)
- `Change Donor` chosen list hidden while open on donation detail page [\#2412](https://github.com/WordImpress/Give/issues/2412)
- Resolve dashboard timeouts due to inefficient stats query for sites with many donations. [\#2383](https://github.com/WordImpress/Give/issues/2383)
- Change Donor option not showing all donors in donation detail page in back-end. [\#2378](https://github.com/WordImpress/Give/issues/2378)
- PHP Notice when changing the donation form title on the donation details page. [\#2377](https://github.com/WordImpress/Give/issues/2377)
- Search functionally in donor dashboard is not working  [\#2371](https://github.com/WordImpress/Give/issues/2371)
- Give 1.8.17 issue with upgrade routine  [\#2356](https://github.com/WordImpress/Give/issues/2356)
- Donate Now button is not preventing click events after clicking on it. [\#2351](https://github.com/WordImpress/Give/issues/2351)
- Give error when entering the same value for set donation and minimum donation amount [\#2348](https://github.com/WordImpress/Give/issues/2348)
- Floating labels don't work in modal or button display mode [\#2341](https://github.com/WordImpress/Give/issues/2341)
- Goal achieved message is not getting display [\#2337](https://github.com/WordImpress/Give/issues/2337)
- Not able to set offline payment as my default option in donation form [\#2336](https://github.com/WordImpress/Give/issues/2336)
- Right to left supported currency formatting issue [\#2332](https://github.com/WordImpress/Give/issues/2332)
- Recalculate income amount and donation counts for all forms not working [\#2319](https://github.com/WordImpress/Give/issues/2319)
- Issue with new getPriceID when levels share the same price [\#2305](https://github.com/WordImpress/Give/issues/2305)
- Problem with email access due to complication with \#1790 [\#2304](https://github.com/WordImpress/Give/issues/2304)
- Text Change in Donations Tools Submenu [\#2299](https://github.com/WordImpress/Give/issues/2299)
- Show 0 amount in goal. [\#2296](https://github.com/WordImpress/Give/issues/2296)
- If more than one form is embedded on the page using radio multi-level amounts it causes jumping [\#2292](https://github.com/WordImpress/Give/issues/2292)
- Run flush\_rewrite\_rules after editing category/tag settings in display options [\#2291](https://github.com/WordImpress/Give/issues/2291)
- Unable to change donation level within donation details edit screen [\#2280](https://github.com/WordImpress/Give/issues/2280)
- Licensing for individual addons that were upgraded are reflecting their original subscriptions, not the upgraded ones [\#2268](https://github.com/WordImpress/Give/issues/2268)
- No Success Message for Bulk Actions under Donation History List in Admin [\#2243](https://github.com/WordImpress/Give/issues/2243)
- Inputs don't focus the cursor correctly on iPhone [\#2239](https://github.com/WordImpress/Give/issues/2239)
- Recalculate Income Amount and Donation Counts for All Forms tool is not working as intended [\#2235](https://github.com/WordImpress/Give/issues/2235)
- Address Fieldset: "State/Province/County" should not go before "City" [\#2226](https://github.com/WordImpress/Give/issues/2226)
- Responsive settings tabs in Dashboard Color issues  [\#2189](https://github.com/WordImpress/Give/issues/2189)
- Settings Tabs needs UI/UX improvement for smaller devices [\#2139](https://github.com/WordImpress/Give/issues/2139)
- Give Importer: Delete Imported Donations doesn't Delete Users created during import [\#2062](https://github.com/WordImpress/Give/issues/2062)

**Closed issues:**

- Getting PHP Notices with WordPress Default widget  [\#2468](https://github.com/WordImpress/Give/issues/2468)
- PHP Warning [\#2466](https://github.com/WordImpress/Give/issues/2466)
- Add 'Times New Roman' font in TCPDF lib for support in PDF Receipt Add-on [\#2438](https://github.com/WordImpress/Give/issues/2438)
- Naming conversion issue in Give Donation list page of dashboard [\#2437](https://github.com/WordImpress/Give/issues/2437)
- Goal Amount Placeholder and field should be 1.00, not 0 [\#2402](https://github.com/WordImpress/Give/issues/2402)
- Donation statuses not following sort applies on donation listing page. [\#2400](https://github.com/WordImpress/Give/issues/2400)
- Reports  Income Not Working Properly after DB update in 2.0 [\#2365](https://github.com/WordImpress/Give/issues/2365)
- Notice displayed on donation for with Give 2.0 [\#2344](https://github.com/WordImpress/Give/issues/2344)
- Give PDF Receipt - Create an End Of Year Report with Donation Details [\#2321](https://github.com/WordImpress/Give/issues/2321)
- Data Tool: Recalculation Income Amount and Donation Counts of all forms flushes all the sales and earnings [\#2300](https://github.com/WordImpress/Give/issues/2300)
- \[Recurring Donations\] PayPal Pro Gateway \(NVP API\) - Request Body Bug [\#2293](https://github.com/WordImpress/Give/issues/2293)
- Use last\_changed meta data for query performance for custom tables [\#2121](https://github.com/WordImpress/Give/issues/2121)
- Add donor id support to email tags [\#1992](https://github.com/WordImpress/Give/issues/1992)
- Allow admin to update donor name properly [\#1715](https://github.com/WordImpress/Give/issues/1715)
- Logged in donations always default to the First Name/Last Name and email of that existing Give Donor. [\#1625](https://github.com/WordImpress/Give/issues/1625)
- Research JS Graphs for Reports Refactor [\#1524](https://github.com/WordImpress/Give/issues/1524)
- Remove type argument in give\_donation\_amount\(\) function call. [\#2481](https://github.com/WordImpress/Give/issues/2481)
- Smoothing out email access UX [\#2474](https://github.com/WordImpress/Give/issues/2474)
- Multi-level and Set amounts as $1.00 default value causes bad UX [\#2454](https://github.com/WordImpress/Give/issues/2454)
- \[give\_delete\_donation\] Check before deleting any payment for quick exit [\#2393](https://github.com/WordImpress/Give/issues/2393)
- \[1.8.17\] Give\_Donors\_Query related notices [\#2391](https://github.com/WordImpress/Give/issues/2391)
- Passing form currency when formatting large goal amount.  [\#2386](https://github.com/WordImpress/Give/issues/2386)
- Disable XDebug to speed up Travis builds [\#2381](https://github.com/WordImpress/Give/issues/2381)
- Prevent duplicate hierarchy from displaying [\#2369](https://github.com/WordImpress/Give/issues/2369)
- Set default donor country to base country [\#2343](https://github.com/WordImpress/Give/issues/2343)
- Enable/Disable category and tag immediately after core setting updates. [\#2328](https://github.com/WordImpress/Give/issues/2328)
- Add new filter for give\_get\_earnings\_by\_date\(\) function. [\#2324](https://github.com/WordImpress/Give/issues/2324)
- Data Tool: Reset the dropdown properly on Recalculate Income Amount and Donation Count for a form [\#2301](https://github.com/WordImpress/Give/issues/2301)
- Multi-level should be the new default donation form type when adding new [\#2281](https://github.com/WordImpress/Give/issues/2281)
- Adding additional currency support and use chosen dropdown to select/search currency list [\#2275](https://github.com/WordImpress/Give/issues/2275)
- Link missing in All Donation Page when there is no donation  [\#2263](https://github.com/WordImpress/Give/issues/2263)
- Make the error message more helpful on the importer. [\#2249](https://github.com/WordImpress/Give/issues/2249)
- Create currency-functions.php to keep all currency related helper functions together [\#2220](https://github.com/WordImpress/Give/issues/2220)
- Add support for dynamic currency [\#2197](https://github.com/WordImpress/Give/issues/2197)
- Fix currency code for "Iranian rial" [\#2175](https://github.com/WordImpress/Give/issues/2175)
- Better handling of custom amount text sent to PayPal [\#2161](https://github.com/WordImpress/Give/issues/2161)
- Reports: Headings and Text Modifications [\#2144](https://github.com/WordImpress/Give/issues/2144)

**Merged pull requests:**

- Release 1.8.17 [\#2482](https://github.com/WordImpress/Give/pull/2482) ([DevinWalker](https://github.com/DevinWalker))
- Revert \#2397 [\#2480](https://github.com/WordImpress/Give/pull/2480) ([ravinderk](https://github.com/ravinderk))
- Receipt Access Improvements [\#2479](https://github.com/WordImpress/Give/pull/2479) ([mehul0810](https://github.com/mehul0810))
- Email access improvements [\#2477](https://github.com/WordImpress/Give/pull/2477) ([DevinWalker](https://github.com/DevinWalker))
- Magnific popup improvements [\#2470](https://github.com/WordImpress/Give/pull/2470) ([DevinWalker](https://github.com/DevinWalker))
- Fix variable checking \#2468 [\#2469](https://github.com/WordImpress/Give/pull/2469) ([raftaar1191](https://github.com/raftaar1191))
- Fix PHP Warning \#2466 [\#2467](https://github.com/WordImpress/Give/pull/2467) ([jaydeeprami](https://github.com/jaydeeprami))
- Remove notice on bulk delete [\#2463](https://github.com/WordImpress/Give/pull/2463) ([mehul0810](https://github.com/mehul0810))
- Issues/2461 [\#2462](https://github.com/WordImpress/Give/pull/2462) ([ravinderk](https://github.com/ravinderk))
- Minorfix/2356 [\#2459](https://github.com/WordImpress/Give/pull/2459) ([mehul0810](https://github.com/mehul0810))
- Issues 1992 [\#2450](https://github.com/WordImpress/Give/pull/2450) ([ravinderk](https://github.com/ravinderk))
- Complete the available currencies for the test data providers [\#2449](https://github.com/WordImpress/Give/pull/2449) ([tw2113](https://github.com/tw2113))
- Issue/2317 [\#2447](https://github.com/WordImpress/Give/pull/2447) ([mehul0810](https://github.com/mehul0810))
- Issue/2440 [\#2444](https://github.com/WordImpress/Give/pull/2444) ([mehul0810](https://github.com/mehul0810))
- Add input box for per page \#2427 [\#2441](https://github.com/WordImpress/Give/pull/2441) ([raftaar1191](https://github.com/raftaar1191))
- time fonts added \#2438 [\#2439](https://github.com/WordImpress/Give/pull/2439) ([jaydeeprami](https://github.com/jaydeeprami))
- Minorfix/issue/1715 [\#2435](https://github.com/WordImpress/Give/pull/2435) ([mehul0810](https://github.com/mehul0810))
- Issues/2432 [\#2433](https://github.com/WordImpress/Give/pull/2433) ([ravinderk](https://github.com/ravinderk))
- Issues/2425 [\#2431](https://github.com/WordImpress/Give/pull/2431) ([ravinderk](https://github.com/ravinderk))
- Issue/2428 [\#2430](https://github.com/WordImpress/Give/pull/2430) ([ravinderk](https://github.com/ravinderk))
- Issue/2363 [\#2422](https://github.com/WordImpress/Give/pull/2422) ([mehul0810](https://github.com/mehul0810))
- Issues/2418 [\#2421](https://github.com/WordImpress/Give/pull/2421) ([ravinderk](https://github.com/ravinderk))
- MinorFix/2416 [\#2417](https://github.com/WordImpress/Give/pull/2417) ([mehul0810](https://github.com/mehul0810))
- Issues/2412 [\#2413](https://github.com/WordImpress/Give/pull/2413) ([ravinderk](https://github.com/ravinderk))
- Issue/2370 [\#2411](https://github.com/WordImpress/Give/pull/2411) ([mehul0810](https://github.com/mehul0810))
- Issue/2407 [\#2410](https://github.com/WordImpress/Give/pull/2410) ([mehul0810](https://github.com/mehul0810))
- Issue/2408 [\#2409](https://github.com/WordImpress/Give/pull/2409) ([mehul0810](https://github.com/mehul0810))
- issue/2403 [\#2404](https://github.com/WordImpress/Give/pull/2404) ([emgk](https://github.com/emgk))
- Issues/2397 [\#2399](https://github.com/WordImpress/Give/pull/2399) ([ravinderk](https://github.com/ravinderk))
- Issue/2086 - Issue with Search and Query Parameters [\#2398](https://github.com/WordImpress/Give/pull/2398) ([mehul0810](https://github.com/mehul0810))
- Issue/2304 [\#2395](https://github.com/WordImpress/Give/pull/2395) ([mehul0810](https://github.com/mehul0810))
- Exit donation delete fn if payment does not exist [\#2394](https://github.com/WordImpress/Give/pull/2394) ([ravinderk](https://github.com/ravinderk))
- Issues/2391 [\#2392](https://github.com/WordImpress/Give/pull/2392) ([ravinderk](https://github.com/ravinderk))
- Issues 2371 [\#2388](https://github.com/WordImpress/Give/pull/2388) ([raftaar1191](https://github.com/raftaar1191))
- issue/2386 [\#2387](https://github.com/WordImpress/Give/pull/2387) ([emgk](https://github.com/emgk))
- Goals without decimals and added filters: issue/2372 [\#2385](https://github.com/WordImpress/Give/pull/2385) ([DevinWalker](https://github.com/DevinWalker))
- 1. Use give\_get\_total\_earnings\(\) for dashboard yearly stats [\#2384](https://github.com/WordImpress/Give/pull/2384) ([DevinWalker](https://github.com/DevinWalker))
- Prevent xDebug on Travis-ci builds [\#2382](https://github.com/WordImpress/Give/pull/2382) ([DevinWalker](https://github.com/DevinWalker))
- Issues 2377 [\#2380](https://github.com/WordImpress/Give/pull/2380) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2366 [\#2367](https://github.com/WordImpress/Give/pull/2367) ([raftaar1191](https://github.com/raftaar1191))
- Issue/2356 [\#2362](https://github.com/WordImpress/Give/pull/2362) ([mehul0810](https://github.com/mehul0810))
- Issue/2161 - Modified payment title content output for PayPal Standard and other gateways using  [\#2355](https://github.com/WordImpress/Give/pull/2355) ([DevinWalker](https://github.com/DevinWalker))
- Add disable button on form submit \#2351 [\#2352](https://github.com/WordImpress/Give/pull/2352) ([raftaar1191](https://github.com/raftaar1191))
- Fix error when user focus out \#2348 [\#2349](https://github.com/WordImpress/Give/pull/2349) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2275 [\#2345](https://github.com/WordImpress/Give/pull/2345) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2341 [\#2342](https://github.com/WordImpress/Give/pull/2342) ([raftaar1191](https://github.com/raftaar1191))
- Fix goal achieved message \#2337 [\#2339](https://github.com/WordImpress/Give/pull/2339) ([raftaar1191](https://github.com/raftaar1191))
- Hotfix/cli generate donor [\#2335](https://github.com/WordImpress/Give/pull/2335) ([ravinderk](https://github.com/ravinderk))
- Hotfix/arabic currencies [\#2333](https://github.com/WordImpress/Give/pull/2333) ([ravinderk](https://github.com/ravinderk))
- Issue/2312 [\#2331](https://github.com/WordImpress/Give/pull/2331) ([mehul0810](https://github.com/mehul0810))
- Issues 2258 [\#2330](https://github.com/WordImpress/Give/pull/2330) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2328 [\#2329](https://github.com/WordImpress/Give/pull/2329) ([ravinderk](https://github.com/ravinderk))
- Issues 2291 [\#2327](https://github.com/WordImpress/Give/pull/2327) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2280 [\#2325](https://github.com/WordImpress/Give/pull/2325) ([raftaar1191](https://github.com/raftaar1191))
- Added filter when getting the donation's earnings between dates. give\_get\_earnings\_by\_date\(\) function. [\#2323](https://github.com/WordImpress/Give/pull/2323) ([emgk](https://github.com/emgk))
- Reset all form stats tool fix [\#2320](https://github.com/WordImpress/Give/pull/2320) ([DevinWalker](https://github.com/DevinWalker))
- Passing form's currency while format the amount. [\#2318](https://github.com/WordImpress/Give/pull/2318) ([emgk](https://github.com/emgk))
- Add filter when getting payment amount through give\_payment\_amount\(\) function. [\#2316](https://github.com/WordImpress/Give/pull/2316) ([emgk](https://github.com/emgk))
- Issues 2310 [\#2313](https://github.com/WordImpress/Give/pull/2313) ([raftaar1191](https://github.com/raftaar1191))
- Added filter when getting donor's purchase value. [\#2309](https://github.com/WordImpress/Give/pull/2309) ([emgk](https://github.com/emgk))
- Issue/2180 [\#2308](https://github.com/WordImpress/Give/pull/2308) ([mehul0810](https://github.com/mehul0810))
- Issues/2305 [\#2307](https://github.com/WordImpress/Give/pull/2307) ([ravinderk](https://github.com/ravinderk))
- Issues 2235 [\#2303](https://github.com/WordImpress/Give/pull/2303) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2263 [\#2302](https://github.com/WordImpress/Give/pull/2302) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2220 [\#2298](https://github.com/WordImpress/Give/pull/2298) ([ravinderk](https://github.com/ravinderk))
- Issues/2296 [\#2297](https://github.com/WordImpress/Give/pull/2297) ([ravinderk](https://github.com/ravinderk))
- Issue/2281 [\#2294](https://github.com/WordImpress/Give/pull/2294) ([mehul0810](https://github.com/mehul0810))
- Pass $form\_id to give\_currency\_filter. [\#2290](https://github.com/WordImpress/Give/pull/2290) ([emgk](https://github.com/emgk))
- Correct spelling of Philippines [\#2289](https://github.com/WordImpress/Give/pull/2289) ([nciske](https://github.com/nciske))
- Issue/2086 - Refactored with Bulk Delete [\#2288](https://github.com/WordImpress/Give/pull/2288) ([mehul0810](https://github.com/mehul0810))
- Added filter when getting the earnings. [\#2287](https://github.com/WordImpress/Give/pull/2287) ([emgk](https://github.com/emgk))
- Issues 2144 [\#2285](https://github.com/WordImpress/Give/pull/2285) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2226 [\#2284](https://github.com/WordImpress/Give/pull/2284) ([raftaar1191](https://github.com/raftaar1191))
- Fix php warning when update total donation to 0 [\#2283](https://github.com/WordImpress/Give/pull/2283) ([jaydeeprami](https://github.com/jaydeeprami))
- Fix Unable to change donation level within donation details edit screen \#2280 [\#2282](https://github.com/WordImpress/Give/pull/2282) ([jaydeeprami](https://github.com/jaydeeprami))
- Minorfix/2086 [\#2276](https://github.com/WordImpress/Give/pull/2276) ([mehul0810](https://github.com/mehul0810))
- MinorFix/Replace  [\#2274](https://github.com/WordImpress/Give/pull/2274) ([mehul0810](https://github.com/mehul0810))
- Issue/2189 [\#2273](https://github.com/WordImpress/Give/pull/2273) ([mehul0810](https://github.com/mehul0810))
- Issue/1709 [\#2272](https://github.com/WordImpress/Give/pull/2272) ([mehul0810](https://github.com/mehul0810))
- Issues 2243 [\#2271](https://github.com/WordImpress/Give/pull/2271) ([raftaar1191](https://github.com/raftaar1191))
- Grd issues 401 [\#2270](https://github.com/WordImpress/Give/pull/2270) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2249 [\#2267](https://github.com/WordImpress/Give/pull/2267) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1625 [\#2264](https://github.com/WordImpress/Give/pull/2264) ([mehul0810](https://github.com/mehul0810))
- Issue/2112 [\#2262](https://github.com/WordImpress/Give/pull/2262) ([mehul0810](https://github.com/mehul0810))
- Made currency global JS variables per form based. [\#2255](https://github.com/WordImpress/Give/pull/2255) ([emgk](https://github.com/emgk))
- Issue/2139 [\#2252](https://github.com/WordImpress/Give/pull/2252) ([mehul0810](https://github.com/mehul0810))
- Issues 2009 - Settings Import/Exporter [\#2236](https://github.com/WordImpress/Give/pull/2236) ([raftaar1191](https://github.com/raftaar1191))
- Issues/370 [\#2233](https://github.com/WordImpress/Give/pull/2233) ([ravinderk](https://github.com/ravinderk))
- Corrected currency code for the "Iranian rial". [\#2174](https://github.com/WordImpress/Give/pull/2174) ([emgk](https://github.com/emgk))

## [1.8.16](https://github.com/WordImpress/Give/tree/1.8.16) (2017-10-27)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.15...1.8.16)

**Implemented enhancements:**

- Add ability to specify certain rows output by the \[donation\_history\] shortcode [\#2156](https://github.com/WordImpress/Give/issues/2156)
- Save currency settings to payment for better currency formatting [\#2153](https://github.com/WordImpress/Give/issues/2153)
- Add email access token logic to "View In Browser" link in Donation Receipt email [\#1790](https://github.com/WordImpress/Give/issues/1790)

**Fixed bugs:**

- Add-on activation banners aren't displaying anymore [\#2241](https://github.com/WordImpress/Give/issues/2241)
- Harden give\_listen\_for\_failed\_payments  [\#2240](https://github.com/WordImpress/Give/issues/2240)
- Donations set to 00:00 time don't show on the "Today" or "Yesterday" filter [\#2221](https://github.com/WordImpress/Give/issues/2221)

**Closed issues:**

- \[give\_form\] ShortCode should support Featured Image [\#2231](https://github.com/WordImpress/Give/issues/2231)
- CC fields incorrectly marking as invalid on page load [\#2244](https://github.com/WordImpress/Give/issues/2244)
- give\_profile\_editor shortcode styling issue  [\#2190](https://github.com/WordImpress/Give/issues/2190)
- Display Notice on saving Donor Info [\#2181](https://github.com/WordImpress/Give/issues/2181)

**Merged pull requests:**

- Release/1.8.16 [\#2261](https://github.com/WordImpress/Give/pull/2261) ([DevinWalker](https://github.com/DevinWalker))
- give\_sanitize\_amount and give\_maybe\_sanitize\_amount allows to sanitize amount based on currency's setting  [\#2257](https://github.com/WordImpress/Give/pull/2257) ([emgk](https://github.com/emgk))
- Issue/2221 [\#2248](https://github.com/WordImpress/Give/pull/2248) ([mehul0810](https://github.com/mehul0810))
- Issues/2244 [\#2247](https://github.com/WordImpress/Give/pull/2247) ([ravinderk](https://github.com/ravinderk))
- Minorfix/1790 [\#2246](https://github.com/WordImpress/Give/pull/2246) ([mehul0810](https://github.com/mehul0810))
- Issues/2240 [\#2245](https://github.com/WordImpress/Give/pull/2245) ([ravinderk](https://github.com/ravinderk))
- Fix Display Notice on saving Donor Info \#2181 [\#2237](https://github.com/WordImpress/Give/pull/2237) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/2086 [\#2234](https://github.com/WordImpress/Give/pull/2234) ([mehul0810](https://github.com/mehul0810))
- Fix multiple form tag on form detail report page [\#2232](https://github.com/WordImpress/Give/pull/2232) ([jaydeeprami](https://github.com/jaydeeprami))
- Hotfix/table [\#2229](https://github.com/WordImpress/Give/pull/2229) ([ravinderk](https://github.com/ravinderk))
- GH\#2190 Fixed CSS break for fieldset and legend tags [\#2228](https://github.com/WordImpress/Give/pull/2228) ([Sidsector9](https://github.com/Sidsector9))
- Issues/2241 [\#2242](https://github.com/WordImpress/Give/pull/2242) ([ravinderk](https://github.com/ravinderk))
- Issue/1790 [\#2186](https://github.com/WordImpress/Give/pull/2186) ([mehul0810](https://github.com/mehul0810))

## [1.8.15](https://github.com/WordImpress/Give/tree/1.8.15) (2017-10-19)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.14...1.8.15)

**Implemented enhancements:**

- Add "All forms" as first option in the "Export Donors as CSV" form dropdown [\#2192](https://github.com/WordImpress/Give/issues/2192)
- Add support for dynamic currency  for donation forms [\#2183](https://github.com/WordImpress/Give/issues/2183)
- Importer: "Postal Code" should auto-map to "Zip" [\#2164](https://github.com/WordImpress/Give/issues/2164)

**Fixed bugs:**

- Tools \> Data: Confirmation Checkbox and Submit button needs to be in sync [\#2195](https://github.com/WordImpress/Give/issues/2195)
- Update list of zero based currency [\#2191](https://github.com/WordImpress/Give/issues/2191)

**Closed issues:**

- Update Copyright Year in all files every year  [\#2216](https://github.com/WordImpress/Give/issues/2216)
- Importer: "Donation Form" should be "Donation Form Title" [\#2165](https://github.com/WordImpress/Give/issues/2165)

**Merged pull requests:**

- Release 1.8.15 with licensing fix and current completed issues [\#2225](https://github.com/WordImpress/Give/pull/2225) ([DevinWalker](https://github.com/DevinWalker))
- Fix php-doc for give\_update\_meta [\#2222](https://github.com/WordImpress/Give/pull/2222) ([jaydeeprami](https://github.com/jaydeeprami))
- Issues 2164 [\#2219](https://github.com/WordImpress/Give/pull/2219) ([raftaar1191](https://github.com/raftaar1191))
- Revert "Map postal code to zip during importing" [\#2218](https://github.com/WordImpress/Give/pull/2218) ([ravinderk](https://github.com/ravinderk))
- Passing ID when getting currency through give\_get\_currency\(\). [\#2214](https://github.com/WordImpress/Give/pull/2214) ([emgk](https://github.com/emgk))
- Add new filter [\#2213](https://github.com/WordImpress/Give/pull/2213) ([ravinderk](https://github.com/ravinderk))
- Issue/2156 [\#2212](https://github.com/WordImpress/Give/pull/2212) ([mehul0810](https://github.com/mehul0810))
- Map postal code to zip during importing [\#2205](https://github.com/WordImpress/Give/pull/2205) ([BhargavBhandari90](https://github.com/BhargavBhandari90))
- Issue/2153 [\#2182](https://github.com/WordImpress/Give/pull/2182) ([mehul0810](https://github.com/mehul0810))
- Fix issue \#2152 [\#2160](https://github.com/WordImpress/Give/pull/2160) ([jaydeeprami](https://github.com/jaydeeprami))

## [1.8.14](https://github.com/WordImpress/Give/tree/1.8.14) (2017-10-16)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.13...1.8.14)

**Implemented enhancements:**

- Easier Registration Process [\#1517](https://github.com/WordImpress/Give/issues/1517)
- Autofill already mapped CSV fields if any error occurred while importing [\#2146](https://github.com/WordImpress/Give/issues/2146)
- Output attachment id when media setting field output set to url [\#2136](https://github.com/WordImpress/Give/issues/2136)
- Importer: After import - sort/insert multi-level amounts within donation forms [\#2123](https://github.com/WordImpress/Give/issues/2123)
- Improve ways to query donors from database [\#2101](https://github.com/WordImpress/Give/issues/2101)
- Add setting to select default state [\#2036](https://github.com/WordImpress/Give/issues/2036)
- Add Donation Importer to allow for Bulk Adding of Donations [\#1966](https://github.com/WordImpress/Give/issues/1966)
- If user is disconnected from associated donor, an error should show when user tries to login [\#1721](https://github.com/WordImpress/Give/issues/1721)
- 'Donation Receipt' is not an accurate heading for a pending or offline donation [\#1396](https://github.com/WordImpress/Give/issues/1396)
- Add a link on Donor Profile to go to User Profile [\#1249](https://github.com/WordImpress/Give/issues/1249)
- Integrate with Akismet for added spam filtering [\#673](https://github.com/WordImpress/Give/issues/673)

**Fixed bugs:**

- Error when viewing Preview Email in New Donor Register and Donor Register [\#2102](https://github.com/WordImpress/Give/issues/2102)
- {fullname} email tag is broken with two-word first names and blank last name [\#1585](https://github.com/WordImpress/Give/issues/1585)
- Give Donor Profile not updating First/Last Name correctly [\#1481](https://github.com/WordImpress/Give/issues/1481)
- PHP Notice when linking new wp user with guest donor. [\#2193](https://github.com/WordImpress/Give/issues/2193)
- PDF of Donations and Income reports not support for various currencies [\#2152](https://github.com/WordImpress/Give/issues/2152)
- PHP Warning: give\_maybe\_sanitize\_amount [\#2147](https://github.com/WordImpress/Give/issues/2147)
- All Forms displays wrong amounts [\#2145](https://github.com/WordImpress/Give/issues/2145)
- Notices not showing in setting page [\#2142](https://github.com/WordImpress/Give/issues/2142)
- Multisite new user activation error when user previously donated [\#2118](https://github.com/WordImpress/Give/issues/2118)
- Minor Alignment Issue on Add New Donation Page with Multi Levels [\#2115](https://github.com/WordImpress/Give/issues/2115)
- Responsive settings tabs are now broken [\#2106](https://github.com/WordImpress/Give/issues/2106)
- Give\_Notices: Non-dismissible notices shouldn't have "x" icon  [\#2100](https://github.com/WordImpress/Give/issues/2100)
- Give Receipt Shortcode attributes does not work as intended [\#2085](https://github.com/WordImpress/Give/issues/2085)
- Add proper text preferably donation amount to Paypal item title instead of custom amount text [\#2071](https://github.com/WordImpress/Give/issues/2071)
- Fix JS issue when clicking on an item within donations list page [\#2051](https://github.com/WordImpress/Give/issues/2051)
- Occational display issue when browser window is resized [\#2000](https://github.com/WordImpress/Give/issues/2000)
- Donation Goal amount is reduced when thousands separator is set to a period [\#1982](https://github.com/WordImpress/Give/issues/1982)

**Closed issues:**

- Conflict with EDD when updating donor's meta using Give\_Donor class instance. [\#2202](https://github.com/WordImpress/Give/issues/2202)
- Fix margin between button and table [\#2176](https://github.com/WordImpress/Give/issues/2176)
- \[Improvement\] Fee recovery checkbox should disappear when offline payment is selected. [\#2169](https://github.com/WordImpress/Give/issues/2169)
- Add helpful descriptions to Importer fields for clarity [\#2130](https://github.com/WordImpress/Give/issues/2130)
- Add Docs link to Importer screen [\#2128](https://github.com/WordImpress/Give/issues/2128)
- New Donor Register in preview is not visible [\#2104](https://github.com/WordImpress/Give/issues/2104)
- Custom Form Fields To Dropdown  [\#2099](https://github.com/WordImpress/Give/issues/2099)
- Recurring Donations Parent Payment link incorrect view parameter [\#2098](https://github.com/WordImpress/Give/issues/2098)
- Using reCAPTCHA on email access fails with more than one shortcode on the page [\#2081](https://github.com/WordImpress/Give/issues/2081)
- Add new sub tab inside Donaitions \> Setting Called CSS and JS [\#2060](https://github.com/WordImpress/Give/issues/2060)
- Donation email stored in payment meta duplicated [\#1148](https://github.com/WordImpress/Give/issues/1148)
- Notice appereance issue [\#2203](https://github.com/WordImpress/Give/issues/2203)
- Donation form section title should be in proper alignment. [\#2187](https://github.com/WordImpress/Give/issues/2187)
- Price formatting issue  with Iranian rial on multi type forms on form listing page [\#2177](https://github.com/WordImpress/Give/issues/2177)
- Exporter: Add filter to donor data [\#2170](https://github.com/WordImpress/Give/issues/2170)
- Prevent download "System Info" window flash when loading tools  [\#2141](https://github.com/WordImpress/Give/issues/2141)
- Import Donation button on Donations listing page conflicts with Give Notice [\#2140](https://github.com/WordImpress/Give/issues/2140)
- Give Importer: Do not import option is shown twice in dropdown [\#2134](https://github.com/WordImpress/Give/issues/2134)
- Change Importer screen settings to radio\_inline and select for consistency [\#2132](https://github.com/WordImpress/Give/issues/2132)
- Importer typos [\#2126](https://github.com/WordImpress/Give/issues/2126)
- Refactor Import Donation tools page  [\#2114](https://github.com/WordImpress/Give/issues/2114)
- give\_profile\_editor shortcode styling issue [\#2097](https://github.com/WordImpress/Give/issues/2097)
- Remove the "Donors" report because it's not helpful [\#2074](https://github.com/WordImpress/Give/issues/2074)
- Give Tools\>Data: Add JS window alert if closing tab or reload the page [\#2069](https://github.com/WordImpress/Give/issues/2069)
- Give Reports section no thousands separator [\#2053](https://github.com/WordImpress/Give/issues/2053)
- All Give admin pages should have a valid H1  [\#2047](https://github.com/WordImpress/Give/issues/2047)
- Rearrange address fieldset in more logical order [\#2037](https://github.com/WordImpress/Give/issues/2037)
- "Give Accountant" user role should be able to access the admin dashboard after logging in with WooCommerce activated [\#2022](https://github.com/WordImpress/Give/issues/2022)
- Changes to settings are lost if not saved when changing between tabs or sub-tabs, no notification gievn [\#1998](https://github.com/WordImpress/Give/issues/1998)
- Consider alternatives to the browser popup for the upgrade script to run [\#1959](https://github.com/WordImpress/Give/issues/1959)
- Repeater amount field shows incorrect decimals for the amount field. [\#1886](https://github.com/WordImpress/Give/issues/1886)

**Merged pull requests:**

- Smoothly render addon activation message [\#2211](https://github.com/WordImpress/Give/pull/2211) ([ravinderk](https://github.com/ravinderk))
- Fix - issue \#2195 [\#2209](https://github.com/WordImpress/Give/pull/2209) ([BhargavBhandari90](https://github.com/BhargavBhandari90))
- Fix - issue \#2192 [\#2207](https://github.com/WordImpress/Give/pull/2207) ([BhargavBhandari90](https://github.com/BhargavBhandari90))
- Fix - issue \#2165 [\#2206](https://github.com/WordImpress/Give/pull/2206) ([BhargavBhandari90](https://github.com/BhargavBhandari90))
- Issues/2203 [\#2204](https://github.com/WordImpress/Give/pull/2204) ([ravinderk](https://github.com/ravinderk))
- Fix license issue [\#2199](https://github.com/WordImpress/Give/pull/2199) ([ravinderk](https://github.com/ravinderk))
- Pass default address value if not exists. [\#2194](https://github.com/WordImpress/Give/pull/2194) ([emgk](https://github.com/emgk))
- Issues 2176 [\#2178](https://github.com/WordImpress/Give/pull/2178) ([raftaar1191](https://github.com/raftaar1191))
- Filter to the set\_donor\_data\(\) method [\#2171](https://github.com/WordImpress/Give/pull/2171) ([allan23](https://github.com/allan23))
- Fix Autofill already mapped CSV fields \#2146 [\#2162](https://github.com/WordImpress/Give/pull/2162) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2140 [\#2158](https://github.com/WordImpress/Give/pull/2158) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2132 [\#2157](https://github.com/WordImpress/Give/pull/2157) ([raftaar1191](https://github.com/raftaar1191))
- Issue 2141 - Adjusted the download "System Settings" notice to prevent it moving [\#2154](https://github.com/WordImpress/Give/pull/2154) ([DevinWalker](https://github.com/DevinWalker))
- Fix wrong currency in donation history [\#2151](https://github.com/WordImpress/Give/pull/2151) ([jaydeeprami](https://github.com/jaydeeprami))
- Issues 2123 [\#2150](https://github.com/WordImpress/Give/pull/2150) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2147 [\#2149](https://github.com/WordImpress/Give/pull/2149) ([ravinderk](https://github.com/ravinderk))
- Remove give-message parameter \#2100 [\#2143](https://github.com/WordImpress/Give/pull/2143) ([raftaar1191](https://github.com/raftaar1191))
- Issues 1959 [\#2138](https://github.com/WordImpress/Give/pull/2138) ([raftaar1191](https://github.com/raftaar1191))
- Issues/2136 [\#2137](https://github.com/WordImpress/Give/pull/2137) ([ravinderk](https://github.com/ravinderk))
- Fixes \#2130 Adding descriptions to Importer fields [\#2131](https://github.com/WordImpress/Give/pull/2131) ([mathetos](https://github.com/mathetos))
- Fixes \#2128 [\#2129](https://github.com/WordImpress/Give/pull/2129) ([mathetos](https://github.com/mathetos))
- Fixes \#2126 [\#2127](https://github.com/WordImpress/Give/pull/2127) ([mathetos](https://github.com/mathetos))
- Fix Alignment \#2115 [\#2125](https://github.com/WordImpress/Give/pull/2125) ([raftaar1191](https://github.com/raftaar1191))
- Issues 1959 [\#2124](https://github.com/WordImpress/Give/pull/2124) ([raftaar1191](https://github.com/raftaar1191))
- Issue/2045 [\#2122](https://github.com/WordImpress/Give/pull/2122) ([mehul0810](https://github.com/mehul0810))
- Output payment objects - Fixes \#2118 [\#2120](https://github.com/WordImpress/Give/pull/2120) ([DevinWalker](https://github.com/DevinWalker))
- Issue/2097 [\#2117](https://github.com/WordImpress/Give/pull/2117) ([mehul0810](https://github.com/mehul0810))
- Show list of import instead of Donation importer [\#2116](https://github.com/WordImpress/Give/pull/2116) ([ravinderk](https://github.com/ravinderk))
- Issues/2101 [\#2111](https://github.com/WordImpress/Give/pull/2111) ([ravinderk](https://github.com/ravinderk))
- Grid CSS for Admin [\#2110](https://github.com/WordImpress/Give/pull/2110) ([mehul0810](https://github.com/mehul0810))
- Fix Non-dismissible notices \#2100 [\#2109](https://github.com/WordImpress/Give/pull/2109) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2102 [\#2107](https://github.com/WordImpress/Give/pull/2107) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1721 [\#2103](https://github.com/WordImpress/Give/pull/2103) ([mehul0810](https://github.com/mehul0810))
- Issue/2085 [\#2095](https://github.com/WordImpress/Give/pull/2095) ([mehul0810](https://github.com/mehul0810))
- Issue/2081 [\#2094](https://github.com/WordImpress/Give/pull/2094) ([DevinWalker](https://github.com/DevinWalker))
- Corrected grammar [\#2092](https://github.com/WordImpress/Give/pull/2092) ([Sidsector9](https://github.com/Sidsector9))
- Changed the layout of the donation receipt for offline payment. \#1396 [\#2090](https://github.com/WordImpress/Give/pull/2090) ([emgk](https://github.com/emgk))
- Issues 1966 [\#2089](https://github.com/WordImpress/Give/pull/2089) ([raftaar1191](https://github.com/raftaar1191))
- Allow admin dashboard to 'Give Accoutant' user role even though WooCommerce is active. [\#2088](https://github.com/WordImpress/Give/pull/2088) ([emgk](https://github.com/emgk))
- QuickFix/2071 [\#2087](https://github.com/WordImpress/Give/pull/2087) ([mehul0810](https://github.com/mehul0810))
- Issues 2037 [\#2084](https://github.com/WordImpress/Give/pull/2084) ([raftaar1191](https://github.com/raftaar1191))
- Fix \#1982 [\#2083](https://github.com/WordImpress/Give/pull/2083) ([ravinderk](https://github.com/ravinderk))
- Fix option to delete WP user \#2062 [\#2080](https://github.com/WordImpress/Give/pull/2080) ([raftaar1191](https://github.com/raftaar1191))
- Fixed: Integrate with Akismet for added spam filtering \#673 [\#2079](https://github.com/WordImpress/Give/pull/2079) ([jaydeeprami](https://github.com/jaydeeprami))
- Improved Responsive UI [\#2078](https://github.com/WordImpress/Give/pull/2078) ([mehul0810](https://github.com/mehul0810))
- Give stripe 98 [\#2077](https://github.com/WordImpress/Give/pull/2077) ([raftaar1191](https://github.com/raftaar1191))
- GH\#2074 Removed donors tab [\#2076](https://github.com/WordImpress/Give/pull/2076) ([Sidsector9](https://github.com/Sidsector9))
- Issue/2071 [\#2075](https://github.com/WordImpress/Give/pull/2075) ([mehul0810](https://github.com/mehul0810))
- GH\#2053 Fixes thousands separator in reports [\#2073](https://github.com/WordImpress/Give/pull/2073) ([Sidsector9](https://github.com/Sidsector9))
- Issue/Stripe/91 [\#2072](https://github.com/WordImpress/Give/pull/2072) ([mehul0810](https://github.com/mehul0810))
- Issues 2064 [\#2068](https://github.com/WordImpress/Give/pull/2068) ([raftaar1191](https://github.com/raftaar1191))
- Fix for give setting page \#1998 [\#2067](https://github.com/WordImpress/Give/pull/2067) ([raftaar1191](https://github.com/raftaar1191))
- Issues 2047 [\#2066](https://github.com/WordImpress/Give/pull/2066) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1249 [\#2059](https://github.com/WordImpress/Give/pull/2059) ([mehul0810](https://github.com/mehul0810))
- Issue/2051 [\#2057](https://github.com/WordImpress/Give/pull/2057) ([mehul0810](https://github.com/mehul0810))
- Issues 2036 [\#2041](https://github.com/WordImpress/Give/pull/2041) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1517 [\#2031](https://github.com/WordImpress/Give/pull/2031) ([mehul0810](https://github.com/mehul0810))
- Issue/1715 [\#1885](https://github.com/WordImpress/Give/pull/1885) ([mehul0810](https://github.com/mehul0810))
- Version 1.8.14 ready for release [\#2198](https://github.com/WordImpress/Give/pull/2198) ([DevinWalker](https://github.com/DevinWalker))
- Fix \#1886 [\#2082](https://github.com/WordImpress/Give/pull/2082) ([ravinderk](https://github.com/ravinderk))
- Issues 2069 [\#2070](https://github.com/WordImpress/Give/pull/2070) ([raftaar1191](https://github.com/raftaar1191))

## [1.8.13](https://github.com/WordImpress/Give/tree/1.8.13) (2017-09-08)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.12...1.8.13)

**Implemented enhancements:**

- Link log to payment instead of form [\#1227](https://github.com/WordImpress/Give/issues/1227)
- Store page ID and URL the donor used to make a donation for future reporting [\#1996](https://github.com/WordImpress/Give/issues/1996)
- When updating a donation form the user should be returned to the last active tab [\#1968](https://github.com/WordImpress/Give/issues/1968)
- Show Pre-Approved payment status even if stripe addon disable [\#1957](https://github.com/WordImpress/Give/issues/1957)
- Add a setting to set the default User Role that donors get when registering and donating [\#1918](https://github.com/WordImpress/Give/issues/1918)

**Fixed bugs:**

- Amount column shows an incorrect price range in all forms table for multi level donation [\#1946](https://github.com/WordImpress/Give/issues/1946)
- Give Logs getting disconnected from their parent forms [\#1286](https://github.com/WordImpress/Give/issues/1286)
- Fix warning within give\_unset\_error error  [\#2049](https://github.com/WordImpress/Give/issues/2049)
- Return `custom` label id from give\_get\_price\_id [\#2042](https://github.com/WordImpress/Give/issues/2042)
- Fresh Give install default settings missing [\#2035](https://github.com/WordImpress/Give/issues/2035)
- License expired/invalid notification issue [\#2034](https://github.com/WordImpress/Give/issues/2034)
- Customer meta value not getting deleted [\#2028](https://github.com/WordImpress/Give/issues/2028)
- PHP Notice: undefined variable "new\_public\_key" and "new\_secret\_key" [\#2024](https://github.com/WordImpress/Give/issues/2024)
- Bottom "Donations" bulk actions not working as expected [\#2008](https://github.com/WordImpress/Give/issues/2008)
- Generating API keys does not resolve 404 on API endpoint [\#1999](https://github.com/WordImpress/Give/issues/1999)
- "Email address ... already active for another user" even when it isnt.  [\#1975](https://github.com/WordImpress/Give/issues/1975)
- PHP Notice Errors [\#1973](https://github.com/WordImpress/Give/issues/1973)
- Give session is not working [\#1971](https://github.com/WordImpress/Give/issues/1971)
- Calculate payment count on basis of active payment status [\#1955](https://github.com/WordImpress/Give/issues/1955)
- Delete payment's log if admin delete payment with tools [\#1954](https://github.com/WordImpress/Give/issues/1954)

**Closed issues:**

- Rename `give\_customers` and `give\_customermeta` db tables [\#1960](https://github.com/WordImpress/Give/issues/1960)
- Add common style for URL field to Give Core. [\#1941](https://github.com/WordImpress/Give/issues/1941)
- Fix error notice during PHPUnit test [\#1833](https://github.com/WordImpress/Give/issues/1833)
- Refactor log [\#1796](https://github.com/WordImpress/Give/issues/1796)
- Clarify structure of payment meta [\#1132](https://github.com/WordImpress/Give/issues/1132)
- Link the donation \# for period to the log for easy user viewing of actual donations for specified period [\#774](https://github.com/WordImpress/Give/issues/774)
- The "Import Donations" should only display on listing page not on donation details [\#2052](https://github.com/WordImpress/Give/issues/2052)
- Suggest move banner, makes UI confusing [\#1997](https://github.com/WordImpress/Give/issues/1997)
- Donation Success and error pages shouldn't be displayed on search results [\#1984](https://github.com/WordImpress/Give/issues/1984)
- PHP Notice related to Yoast clear sitemaps function [\#1977](https://github.com/WordImpress/Give/issues/1977)
- Prevent flash of multi-level fields when form editor loads [\#1969](https://github.com/WordImpress/Give/issues/1969)
- Create PHPunit helper function API [\#1967](https://github.com/WordImpress/Give/issues/1967)
- Twenty Seventeen "dark" theme colors default are just not good. [\#1962](https://github.com/WordImpress/Give/issues/1962)
- Multi-level dropdown needs max-width [\#1952](https://github.com/WordImpress/Give/issues/1952)
- Fail Gracefully for release 1.8.13 in preparation for 2.0 with PHP 5.2 [\#1931](https://github.com/WordImpress/Give/issues/1931)
- When no list items in the tables display an informative graphic [\#1917](https://github.com/WordImpress/Give/issues/1917)

**Merged pull requests:**

- Remove import donation link from donation single page \#2052 [\#2055](https://github.com/WordImpress/Give/pull/2055) ([raftaar1191](https://github.com/raftaar1191))
- Release/1.8.13 [\#2054](https://github.com/WordImpress/Give/pull/2054) ([DevinWalker](https://github.com/DevinWalker))
- Fix notices \#2049 [\#2050](https://github.com/WordImpress/Give/pull/2050) ([raftaar1191](https://github.com/raftaar1191))
- MinorFix/1997 [\#2048](https://github.com/WordImpress/Give/pull/2048) ([mehul0810](https://github.com/mehul0810))
- MinorFix/2034 [\#2046](https://github.com/WordImpress/Give/pull/2046) ([mehul0810](https://github.com/mehul0810))
- Issue/2034 [\#2044](https://github.com/WordImpress/Give/pull/2044) ([mehul0810](https://github.com/mehul0810))
- Return custom level id from give\_get\_price\_id [\#2043](https://github.com/WordImpress/Give/pull/2043) ([ravinderk](https://github.com/ravinderk))
- Unittest/2035 [\#2040](https://github.com/WordImpress/Give/pull/2040) ([mehul0810](https://github.com/mehul0810))
- GH\#2035 Added default decimal separator [\#2039](https://github.com/WordImpress/Give/pull/2039) ([Sidsector9](https://github.com/Sidsector9))
- Issue/2024 [\#2033](https://github.com/WordImpress/Give/pull/2033) ([mehul0810](https://github.com/mehul0810))
- Issues 2028 [\#2030](https://github.com/WordImpress/Give/pull/2030) ([raftaar1191](https://github.com/raftaar1191))
- change [\#2029](https://github.com/WordImpress/Give/pull/2029) ([Umangvaghela](https://github.com/Umangvaghela))
- Validate [\#2027](https://github.com/WordImpress/Give/pull/2027) ([Umangvaghela](https://github.com/Umangvaghela))
- add command [\#2026](https://github.com/WordImpress/Give/pull/2026) ([Umangvaghela](https://github.com/Umangvaghela))
- add wp give donors example to list all donors [\#2025](https://github.com/WordImpress/Give/pull/2025) ([Umangvaghela](https://github.com/Umangvaghela))
- add changes [\#2021](https://github.com/WordImpress/Give/pull/2021) ([Umangvaghela](https://github.com/Umangvaghela))
- Issues/1227 [\#2019](https://github.com/WordImpress/Give/pull/2019) ([ravinderk](https://github.com/ravinderk))
- Feature/phpunit [\#2018](https://github.com/WordImpress/Give/pull/2018) ([ravinderk](https://github.com/ravinderk))
- Issue/1918 [\#2017](https://github.com/WordImpress/Give/pull/2017) ([mehul0810](https://github.com/mehul0810))
- Issue/1997 [\#2016](https://github.com/WordImpress/Give/pull/2016) ([mehul0810](https://github.com/mehul0810))
- QuickFix/Recurring/Issue/145 [\#2015](https://github.com/WordImpress/Give/pull/2015) ([mehul0810](https://github.com/mehul0810))
- i18n: Avoid using HTML tags in translation strings [\#2014](https://github.com/WordImpress/Give/pull/2014) ([ramiy](https://github.com/ramiy))
- Issue/1975 [\#2011](https://github.com/WordImpress/Give/pull/2011) ([mehul0810](https://github.com/mehul0810))
- Issues 1996 [\#2003](https://github.com/WordImpress/Give/pull/2003) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1917 [\#2002](https://github.com/WordImpress/Give/pull/2002) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issues 1966 [\#2001](https://github.com/WordImpress/Give/pull/2001) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1968 [\#1995](https://github.com/WordImpress/Give/pull/1995) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issues/1953 [\#1994](https://github.com/WordImpress/Give/pull/1994) ([ravinderk](https://github.com/ravinderk))
- Issue/1984 [\#1986](https://github.com/WordImpress/Give/pull/1986) ([DevinWalker](https://github.com/DevinWalker))
- Issues 1954 [\#1985](https://github.com/WordImpress/Give/pull/1985) ([raftaar1191](https://github.com/raftaar1191))
- remove \[type=“url”\] from float-labels exclude option [\#1980](https://github.com/WordImpress/Give/pull/1980) ([pryley](https://github.com/pryley))
- Feature/donor\_tables [\#1979](https://github.com/WordImpress/Give/pull/1979) ([ravinderk](https://github.com/ravinderk))
- Issue/1977 [\#1978](https://github.com/WordImpress/Give/pull/1978) ([DevinWalker](https://github.com/DevinWalker))
- Upgrade Give to float-labels v3.0.3 [\#1976](https://github.com/WordImpress/Give/pull/1976) ([pryley](https://github.com/pryley))
- Issue/1957 [\#1974](https://github.com/WordImpress/Give/pull/1974) ([mehul0810](https://github.com/mehul0810))
- Issue/1952 [\#1965](https://github.com/WordImpress/Give/pull/1965) ([mehul0810](https://github.com/mehul0810))
- Fix delete log \#1935 [\#1961](https://github.com/WordImpress/Give/pull/1961) ([raftaar1191](https://github.com/raftaar1191))
- Feature/meta tables [\#1958](https://github.com/WordImpress/Give/pull/1958) ([ravinderk](https://github.com/ravinderk))
- Payments unit tests [\#1951](https://github.com/WordImpress/Give/pull/1951) ([DevinWalker](https://github.com/DevinWalker))
- Show only minimum php version notice \(5.2\)  and Do not load plugin [\#1949](https://github.com/WordImpress/Give/pull/1949) ([ravinderk](https://github.com/ravinderk))
- Spelling Corrections [\#1948](https://github.com/WordImpress/Give/pull/1948) ([garrett-eclipse](https://github.com/garrett-eclipse))
- Sanitizing donation level amounts before comparision [\#1947](https://github.com/WordImpress/Give/pull/1947) ([ravinderk](https://github.com/ravinderk))
- Issue/1941 [\#1943](https://github.com/WordImpress/Give/pull/1943) ([mehul0810](https://github.com/mehul0810))
- Refactor payment meta [\#1794](https://github.com/WordImpress/Give/pull/1794) ([ravinderk](https://github.com/ravinderk))
- Issue/2008 [\#2013](https://github.com/WordImpress/Give/pull/2013) ([mehul0810](https://github.com/mehul0810))
- Refactor Give\_Cron [\#2010](https://github.com/WordImpress/Give/pull/2010) ([ravinderk](https://github.com/ravinderk))
- Add some guidelines for how to test well you code before creating PR [\#2007](https://github.com/WordImpress/Give/pull/2007) ([ravinderk](https://github.com/ravinderk))
- Issue/1999 [\#2005](https://github.com/WordImpress/Give/pull/2005) ([mehul0810](https://github.com/mehul0810))
- Issues/1967 [\#1991](https://github.com/WordImpress/Give/pull/1991) ([ravinderk](https://github.com/ravinderk))
- Issues/1973 [\#1990](https://github.com/WordImpress/Give/pull/1990) ([ravinderk](https://github.com/ravinderk))
- Performance [\#1989](https://github.com/WordImpress/Give/pull/1989) ([ravinderk](https://github.com/ravinderk))
- Use donor id instead of user email or user id [\#1988](https://github.com/WordImpress/Give/pull/1988) ([ravinderk](https://github.com/ravinderk))
- Issues/1981 Issues/1982 [\#1983](https://github.com/WordImpress/Give/pull/1983) ([ravinderk](https://github.com/ravinderk))
- Issues/1971 [\#1972](https://github.com/WordImpress/Give/pull/1972) ([ravinderk](https://github.com/ravinderk))
- Fix \#1955 [\#1956](https://github.com/WordImpress/Give/pull/1956) ([ravinderk](https://github.com/ravinderk))

## [1.8.12](https://github.com/WordImpress/Give/tree/1.8.12) (2017-08-02)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.11...1.8.12)

**Implemented enhancements:**

- Allow Goal to be set as number of Donations [\#1443](https://github.com/WordImpress/Give/issues/1443)
- Donation Form Goal option under Give Shortcodes editor button shows all forms [\#1898](https://github.com/WordImpress/Give/issues/1898)
- Add Donation History page url to System Info [\#1841](https://github.com/WordImpress/Give/issues/1841)
- WP List Column: "Donations" link should go to payments screen with filter [\#1824](https://github.com/WordImpress/Give/issues/1824)
- Update System Info with PayPal IPN setting [\#1787](https://github.com/WordImpress/Give/issues/1787)
- Donation Methods Report Columns should be Sortable [\#1616](https://github.com/WordImpress/Give/issues/1616)
- Better way to notify user about updates and upgrades [\#1538](https://github.com/WordImpress/Give/issues/1538)
- Allow admin to filter donations by form id [\#1199](https://github.com/WordImpress/Give/issues/1199)
- Dynamically hide/show and unrequire/require state fields based on country. [\#1050](https://github.com/WordImpress/Give/issues/1050)
- Add check for TLS 1.2 support  [\#810](https://github.com/WordImpress/Give/issues/810)

**Fixed bugs:**

- Ensure country required state/county conditional field is properly required or not required [\#1935](https://github.com/WordImpress/Give/issues/1935)
- Fix total donated amount value in donation method report page [\#1933](https://github.com/WordImpress/Give/issues/1933)
- {user\_email} and {billing\_address} does not reflect value within Email or Preview Donation Receipt [\#1929](https://github.com/WordImpress/Give/issues/1929)
- Incorrect currency displaying under Donations \> Donors [\#1925](https://github.com/WordImpress/Give/issues/1925)
- Update routines: Required parameter "$current\_total" missing [\#1924](https://github.com/WordImpress/Give/issues/1924)
- Changing level of existing Multilevel donation is buggy [\#1911](https://github.com/WordImpress/Give/issues/1911)
- Recalculate Income Amount and Donation Counts for All Forms tool is not working [\#1905](https://github.com/WordImpress/Give/issues/1905)
- Delete data on Uninstall prevents deletion [\#1900](https://github.com/WordImpress/Give/issues/1900)
- Variable price is not loading on multi type form change on payment detail page [\#1894](https://github.com/WordImpress/Give/issues/1894)
- Unable to view Renewal payment listing [\#1891](https://github.com/WordImpress/Give/issues/1891)
- Admin Dashboard Donation form issues [\#1874](https://github.com/WordImpress/Give/issues/1874)
- When period used as thousands separator, the total is wrong and minimum amount alert triggers [\#1849](https://github.com/WordImpress/Give/issues/1849)
- Pagination for listing in admin is not working through a custom page number [\#1847](https://github.com/WordImpress/Give/issues/1847)
- Activate License Notice UI issue under Plugins for Multisite setup [\#1844](https://github.com/WordImpress/Give/issues/1844)
- Email tag in emails are not converting. [\#1793](https://github.com/WordImpress/Give/issues/1793)
- Pagination incorrect on any instance of extending the WP\_List\_Table in the give back end [\#1378](https://github.com/WordImpress/Give/issues/1378)

**Closed issues:**

- assets/js/frontend/give.all.min.js does not work [\#1928](https://github.com/WordImpress/Give/issues/1928)
- Database error in Donations pages [\#1888](https://github.com/WordImpress/Give/issues/1888)
- Phpcs PHPCompatibility issues [\#1860](https://github.com/WordImpress/Give/issues/1860)
- Update readme install count  [\#1921](https://github.com/WordImpress/Give/issues/1921)
- Scroll icons display incorrectly on Windows within the Form edit Sub-tab items on flyout [\#1910](https://github.com/WordImpress/Give/issues/1910)
- Deprecate give\_get\_purchase\_summary\(\) and improve function [\#1902](https://github.com/WordImpress/Give/issues/1902)
- Improve CSS of Donations List Filters [\#1901](https://github.com/WordImpress/Give/issues/1901)
- Touch up content styles for new Updates screen  [\#1896](https://github.com/WordImpress/Give/issues/1896)
- Prevent background scrolling on iPhone Safari [\#1866](https://github.com/WordImpress/Give/issues/1866)
- Add PHPUnit test for email tag functionality [\#1843](https://github.com/WordImpress/Give/issues/1843)
- Update Receipt language for Offline Donation Instructions to match other instances [\#1840](https://github.com/WordImpress/Give/issues/1840)
- Export screen CSS issues [\#1838](https://github.com/WordImpress/Give/issues/1838)
- Implement auto hide functionality for frontend notice in Give\_Notices [\#1837](https://github.com/WordImpress/Give/issues/1837)
- Including ESLint for JS coding standards [\#1827](https://github.com/WordImpress/Give/issues/1827)
- Don't display donation payment status if no payments are within that status [\#1823](https://github.com/WordImpress/Give/issues/1823)
- New data tool to delete all donors and their payments, keep the donation forms [\#1700](https://github.com/WordImpress/Give/issues/1700)
- update\_customer\_email\_on\_user\_update adds itself as an action [\#1358](https://github.com/WordImpress/Give/issues/1358)

**Merged pull requests:**

- Release/1.8.12 merge in Master for Release [\#1942](https://github.com/WordImpress/Give/pull/1942) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1843 [\#1940](https://github.com/WordImpress/Give/pull/1940) ([DevinWalker](https://github.com/DevinWalker))
- Prevent scrolling background within iOS safari - Fixes \#1866 [\#1939](https://github.com/WordImpress/Give/pull/1939) ([DevinWalker](https://github.com/DevinWalker))
- Issues 1935 [\#1937](https://github.com/WordImpress/Give/pull/1937) ([raftaar1191](https://github.com/raftaar1191))
- issues/1933 [\#1934](https://github.com/WordImpress/Give/pull/1934) ([ravinderk](https://github.com/ravinderk))
- Query Improvement [\#1932](https://github.com/WordImpress/Give/pull/1932) ([ravinderk](https://github.com/ravinderk))
- Fix issue 1929 [\#1930](https://github.com/WordImpress/Give/pull/1930) ([jaydeeprami](https://github.com/jaydeeprami))
- Issues/1925 [\#1927](https://github.com/WordImpress/Give/pull/1927) ([ravinderk](https://github.com/ravinderk))
- Issues/1924 [\#1926](https://github.com/WordImpress/Give/pull/1926) ([ravinderk](https://github.com/ravinderk))
- Issue/1910 [\#1923](https://github.com/WordImpress/Give/pull/1923) ([mehul0810](https://github.com/mehul0810))
- Hotfix - Comment Improvement [\#1922](https://github.com/WordImpress/Give/pull/1922) ([mehul0810](https://github.com/mehul0810))
- Updates to unit tests travis ci fixes and upgrades routine for php 5.2 compat [\#1919](https://github.com/WordImpress/Give/pull/1919) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1849 [\#1916](https://github.com/WordImpress/Give/pull/1916) ([ravinderk](https://github.com/ravinderk))
- Fix phpunit fail cause [\#1915](https://github.com/WordImpress/Give/pull/1915) ([ravinderk](https://github.com/ravinderk))
- Issue/1911 [\#1914](https://github.com/WordImpress/Give/pull/1914) ([mehul0810](https://github.com/mehul0810))
- Issue/1896 [\#1912](https://github.com/WordImpress/Give/pull/1912) ([DevinWalker](https://github.com/DevinWalker))
- Issues 1900 [\#1907](https://github.com/WordImpress/Give/pull/1907) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1898 [\#1906](https://github.com/WordImpress/Give/pull/1906) ([mehul0810](https://github.com/mehul0810))
- Issue/1901 [\#1904](https://github.com/WordImpress/Give/pull/1904) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1902 [\#1903](https://github.com/WordImpress/Give/pull/1903) ([DevinWalker](https://github.com/DevinWalker))
- Issues 1700 [\#1897](https://github.com/WordImpress/Give/pull/1897) ([raftaar1191](https://github.com/raftaar1191))
- Fix: \#1894 [\#1895](https://github.com/WordImpress/Give/pull/1895) ([ravinderk](https://github.com/ravinderk))
- Fix: apply filters before give\_pre\_get\_payments hook [\#1892](https://github.com/WordImpress/Give/pull/1892) ([ravinderk](https://github.com/ravinderk))
- Issues/1538 [\#1890](https://github.com/WordImpress/Give/pull/1890) ([ravinderk](https://github.com/ravinderk))
- Fix Database error \#1888 [\#1889](https://github.com/WordImpress/Give/pull/1889) ([raftaar1191](https://github.com/raftaar1191))
- Issues 1050 [\#1887](https://github.com/WordImpress/Give/pull/1887) ([raftaar1191](https://github.com/raftaar1191))
- Hotfix for Issue 1824 [\#1884](https://github.com/WordImpress/Give/pull/1884) ([mehul0810](https://github.com/mehul0810))
- Hotfix/1824 [\#1883](https://github.com/WordImpress/Give/pull/1883) ([mehul0810](https://github.com/mehul0810))
- Refactor: Give\_Setting\_Gateways ---\> Give\_Gateways\_Report [\#1881](https://github.com/WordImpress/Give/pull/1881) ([ravinderk](https://github.com/ravinderk))
- Issue/1249 [\#1880](https://github.com/WordImpress/Give/pull/1880) ([mehul0810](https://github.com/mehul0810))
- Issue/1443 [\#1879](https://github.com/WordImpress/Give/pull/1879) ([mehul0810](https://github.com/mehul0810))
- Added TLS check to System Info Issue/810 [\#1878](https://github.com/WordImpress/Give/pull/1878) ([DevinWalker](https://github.com/DevinWalker))
- Issues 1874 [\#1877](https://github.com/WordImpress/Give/pull/1877) ([raftaar1191](https://github.com/raftaar1191))
- Issues 1823 [\#1875](https://github.com/WordImpress/Give/pull/1875) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1616 [\#1872](https://github.com/WordImpress/Give/pull/1872) ([mehul0810](https://github.com/mehul0810))
- Issue/1844 [\#1870](https://github.com/WordImpress/Give/pull/1870) ([mehul0810](https://github.com/mehul0810))
- Issue/1827 [\#1869](https://github.com/WordImpress/Give/pull/1869) ([mehul0810](https://github.com/mehul0810))
- Issue/1840 [\#1868](https://github.com/WordImpress/Give/pull/1868) ([mehul0810](https://github.com/mehul0810))
- Issue/1824 [\#1867](https://github.com/WordImpress/Give/pull/1867) ([mehul0810](https://github.com/mehul0810))
- Change text domain "edit" to "give" [\#1865](https://github.com/WordImpress/Give/pull/1865) ([dixitadusara](https://github.com/dixitadusara))
- Fix: \# 1358 [\#1864](https://github.com/WordImpress/Give/pull/1864) ([ravinderk](https://github.com/ravinderk))
- Issue/1847 [\#1863](https://github.com/WordImpress/Give/pull/1863) ([mehul0810](https://github.com/mehul0810))
- Issue/1841 [\#1859](https://github.com/WordImpress/Give/pull/1859) ([mehul0810](https://github.com/mehul0810))
- Issues 1050 [\#1858](https://github.com/WordImpress/Give/pull/1858) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1838 [\#1857](https://github.com/WordImpress/Give/pull/1857) ([mehul0810](https://github.com/mehul0810))
- Issue/1787 [\#1856](https://github.com/WordImpress/Give/pull/1856) ([mehul0810](https://github.com/mehul0810))
- Pagination Fix for Issue/1378 [\#1855](https://github.com/WordImpress/Give/pull/1855) ([mehul0810](https://github.com/mehul0810))

## [1.8.11](https://github.com/WordImpress/Give/tree/1.8.11) (2017-07-11)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.10...1.8.11)

**Closed issues:**

- Fix conflict with Yoast SEO's new Link Checker tool on fresh Give Install [\#1848](https://github.com/WordImpress/Give/issues/1848)

**Merged pull requests:**

- Release/1.8.11 [\#1854](https://github.com/WordImpress/Give/pull/1854) ([DevinWalker](https://github.com/DevinWalker))
- Fix Yoast SEO Link Checker Conflict \#1848 [\#1852](https://github.com/WordImpress/Give/pull/1852) ([DevinWalker](https://github.com/DevinWalker))
- Update: add auto dismissible feature to frontend notices [\#1846](https://github.com/WordImpress/Give/pull/1846) ([ravinderk](https://github.com/ravinderk))

## [1.8.10](https://github.com/WordImpress/Give/tree/1.8.10) (2017-07-11)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.9...1.8.10)

**Implemented enhancements:**

- Add login notification upon successful login within donation form [\#1384](https://github.com/WordImpress/Give/issues/1384)

**Fixed bugs:**

- Non-core email tags not working in Give 1.8.9 [\#1839](https://github.com/WordImpress/Give/issues/1839)

**Merged pull requests:**

- Release/1.8.10 [\#1845](https://github.com/WordImpress/Give/pull/1845) ([DevinWalker](https://github.com/DevinWalker))
- Fix: load email tags earlier [\#1842](https://github.com/WordImpress/Give/pull/1842) ([ravinderk](https://github.com/ravinderk))
- WIP: Issues 1384 [\#1836](https://github.com/WordImpress/Give/pull/1836) ([raftaar1191](https://github.com/raftaar1191))
- Issue/1378 [\#1835](https://github.com/WordImpress/Give/pull/1835) ([mehul0810](https://github.com/mehul0810))
- Issues/1796 [\#1797](https://github.com/WordImpress/Give/pull/1797) ([ravinderk](https://github.com/ravinderk))

## [1.8.9](https://github.com/WordImpress/Give/tree/1.8.9) (2017-07-06)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.8...1.8.9)

**Implemented enhancements:**

- Credit Card Expiry Date Format  [\#1781](https://github.com/WordImpress/Give/issues/1781)
- Specify timeframe for "Export Donors" option on export tab. [\#1427](https://github.com/WordImpress/Give/issues/1427)
- Accessing Goals from the API [\#1423](https://github.com/WordImpress/Give/issues/1423)
- Require stronger password for Give Registration [\#1305](https://github.com/WordImpress/Give/issues/1305)

**Fixed bugs:**

- Show error message while visiting receipt page without payment\_key [\#1484](https://github.com/WordImpress/Give/issues/1484)
- WP List Column: Donor's donation count column incorrect link [\#1830](https://github.com/WordImpress/Give/issues/1830)
- WP List Column: "Income" links are broken [\#1825](https://github.com/WordImpress/Give/issues/1825)
- Emails: New username and registration emails missing heading  [\#1821](https://github.com/WordImpress/Give/issues/1821)
- give\_count\_payments is not working when pass \#{payment\_id} [\#1817](https://github.com/WordImpress/Give/issues/1817)
- Licensing Issue with Give Fee Recovery Addon [\#1816](https://github.com/WordImpress/Give/issues/1816)
- Give\_Payment\_Query class issue [\#1813](https://github.com/WordImpress/Give/issues/1813)
- Multiple time give\_update\_edited\_donation hook [\#1811](https://github.com/WordImpress/Give/issues/1811)
- Update message showing incorrectly on fresh WP + Give install [\#1810](https://github.com/WordImpress/Give/issues/1810)
- No "Processing" filter and "Set To Processing" option in Donation Page [\#1803](https://github.com/WordImpress/Give/issues/1803)
- Invalid entry created on bulk action under Donations [\#1801](https://github.com/WordImpress/Give/issues/1801)
- Broken link to documentation within readme.txt [\#1791](https://github.com/WordImpress/Give/issues/1791)
- Issue with Install After Initializing via plugins\_loaded from PR \#1767 [\#1772](https://github.com/WordImpress/Give/issues/1772)
- Translation banner showing incorrectly for some users [\#1770](https://github.com/WordImpress/Give/issues/1770)
- Per User Language Setting Bug [\#1769](https://github.com/WordImpress/Give/issues/1769)
- Notice: Undefined index in /wp-content/plugins/give/templates/shortcode-receipt.php on line 38 [\#1763](https://github.com/WordImpress/Give/issues/1763)
- "File" and "Media" settings field types are synonymous  [\#1758](https://github.com/WordImpress/Give/issues/1758)
- Currency raw HTML character output appears within gateways  [\#1757](https://github.com/WordImpress/Give/issues/1757)
- Editing donor shouldn't detach user [\#1751](https://github.com/WordImpress/Give/issues/1751)
- If donor is using an additional email and the same email used to register user from donation form creates conflict [\#1722](https://github.com/WordImpress/Give/issues/1722)
- Show correct donor name on donation detail page [\#1716](https://github.com/WordImpress/Give/issues/1716)
- Fix admin ajax warning's false positives or remove altogether [\#1631](https://github.com/WordImpress/Give/issues/1631)
- WPML Compatiblity  [\#1609](https://github.com/WordImpress/Give/issues/1609)
- Recalculate for ALL Forms doesn't work for All forms [\#1554](https://github.com/WordImpress/Give/issues/1554)
- Sorting by minimum amount [\#1253](https://github.com/WordImpress/Give/issues/1253)
- Fix API request log count [\#1069](https://github.com/WordImpress/Give/issues/1069)
- Fix \#1811 [\#1812](https://github.com/WordImpress/Give/pull/1812) ([jaydeeprami](https://github.com/jaydeeprami))

**Closed issues:**

- \[Request\] Filter by Payment Type [\#1783](https://github.com/WordImpress/Give/issues/1783)
- Add prefix to on\_create\_blog\(\) [\#1809](https://github.com/WordImpress/Give/issues/1809)
- Add Confirmation popup to Resend Receipt on Donation List page [\#1802](https://github.com/WordImpress/Give/issues/1802)
- Invalid text-domain "easy-digital-downloads" [\#1784](https://github.com/WordImpress/Give/issues/1784)
- Add confirmation alert when deleting donations with hover link [\#1773](https://github.com/WordImpress/Give/issues/1773)
- Include "Donation Forms" within WP-admin Menus by Default On Install  [\#1765](https://github.com/WordImpress/Give/issues/1765)
- Fail gracefully with unsupported PHP versions. [\#1723](https://github.com/WordImpress/Give/issues/1723)
- Compatibility with Yoast SEO when Form Single Views are disabled [\#1690](https://github.com/WordImpress/Give/issues/1690)

**Merged pull requests:**

- Release/1.8.9 merging to master for release [\#1832](https://github.com/WordImpress/Give/pull/1832) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1830 [\#1831](https://github.com/WordImpress/Give/pull/1831) ([DevinWalker](https://github.com/DevinWalker))
- Hotfix/1690 [\#1829](https://github.com/WordImpress/Give/pull/1829) ([mehul0810](https://github.com/mehul0810))
- Issues/1817 [\#1828](https://github.com/WordImpress/Give/pull/1828) ([ravinderk](https://github.com/ravinderk))
- Issue/1825 [\#1826](https://github.com/WordImpress/Give/pull/1826) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1821 [\#1822](https://github.com/WordImpress/Give/pull/1822) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1816 [\#1820](https://github.com/WordImpress/Give/pull/1820) ([ravinderk](https://github.com/ravinderk))
- Hotfix/1801 [\#1819](https://github.com/WordImpress/Give/pull/1819) ([mehul0810](https://github.com/mehul0810))
- issues/1817 [\#1818](https://github.com/WordImpress/Give/pull/1818) ([ravinderk](https://github.com/ravinderk))
- Issues/1813 [\#1815](https://github.com/WordImpress/Give/pull/1815) ([ravinderk](https://github.com/ravinderk))
- Prefix on\_create\_blog\(\) function [\#1814](https://github.com/WordImpress/Give/pull/1814) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1801 [\#1808](https://github.com/WordImpress/Give/pull/1808) ([mehul0810](https://github.com/mehul0810))
- Fix text changes in CONTRIBUTING.md file [\#1806](https://github.com/WordImpress/Give/pull/1806) ([dixitadusara](https://github.com/dixitadusara))
- Issue/1802 [\#1805](https://github.com/WordImpress/Give/pull/1805) ([mehul0810](https://github.com/mehul0810))
- Fixed \#1803 [\#1804](https://github.com/WordImpress/Give/pull/1804) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/1631 [\#1799](https://github.com/WordImpress/Give/pull/1799) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1793 [\#1795](https://github.com/WordImpress/Give/pull/1795) ([mehul0810](https://github.com/mehul0810))
- issue/1423 - With Code Updates from Devin Walker [\#1792](https://github.com/WordImpress/Give/pull/1792) ([DevinWalker](https://github.com/DevinWalker))
- Hotfix - Irrelevant comment [\#1789](https://github.com/WordImpress/Give/pull/1789) ([mehul0810](https://github.com/mehul0810))
- Issues/1781 [\#1788](https://github.com/WordImpress/Give/pull/1788) ([ravinderk](https://github.com/ravinderk))
- Fix issue \#1784 [\#1786](https://github.com/WordImpress/Give/pull/1786) ([jaydeeprami](https://github.com/jaydeeprami))
- Issue/1773 [\#1782](https://github.com/WordImpress/Give/pull/1782) ([mehul0810](https://github.com/mehul0810))
- Issue/1427 [\#1777](https://github.com/WordImpress/Give/pull/1777) ([mehul0810](https://github.com/mehul0810))
- i18n Banner Displays Per User's Locale [\#1776](https://github.com/WordImpress/Give/pull/1776) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1769 [\#1775](https://github.com/WordImpress/Give/pull/1775) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1772 - Give initialization improvement and resolving install issue [\#1774](https://github.com/WordImpress/Give/pull/1774) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1554 [\#1771](https://github.com/WordImpress/Give/pull/1771) ([mehul0810](https://github.com/mehul0810))
- fixed enabled donation form issue on nav menu. [\#1768](https://github.com/WordImpress/Give/pull/1768) ([emgk](https://github.com/emgk))
- Ensure Give\(\) function is run on `plugin\_loaded` action hook so other… [\#1767](https://github.com/WordImpress/Give/pull/1767) ([DevinWalker](https://github.com/DevinWalker))
- Check that the payment exists prior to outputting content. If it does… [\#1766](https://github.com/WordImpress/Give/pull/1766) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1722 [\#1762](https://github.com/WordImpress/Give/pull/1762) ([mehul0810](https://github.com/mehul0810))
- Issue/1758 [\#1761](https://github.com/WordImpress/Give/pull/1761) ([ravinderk](https://github.com/ravinderk))
- Fixed login and register phpunit tests [\#1760](https://github.com/WordImpress/Give/pull/1760) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1757 [\#1759](https://github.com/WordImpress/Give/pull/1759) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1723 [\#1756](https://github.com/WordImpress/Give/pull/1756) ([mehul0810](https://github.com/mehul0810))
- Issue/1305 [\#1754](https://github.com/WordImpress/Give/pull/1754) ([mehul0810](https://github.com/mehul0810))
- Issue/1751 [\#1753](https://github.com/WordImpress/Give/pull/1753) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1253 [\#1752](https://github.com/WordImpress/Give/pull/1752) ([mehul0810](https://github.com/mehul0810))
- Issue/1690 [\#1750](https://github.com/WordImpress/Give/pull/1750) ([mehul0810](https://github.com/mehul0810))
- Issue/896 deprecate \*\_Customer\_\* classes for \*\_Donor\_\* classes [\#1739](https://github.com/WordImpress/Give/pull/1739) ([DevinWalker](https://github.com/DevinWalker))
- Bye bye Qtip2 and hello Hint.css Tooltips [\#1596](https://github.com/WordImpress/Give/pull/1596) ([DevinWalker](https://github.com/DevinWalker))

## [1.8.8](https://github.com/WordImpress/Give/tree/1.8.8) (2017-05-30)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.7.1...1.8.8)

**Implemented enhancements:**

- Check if we can create email notification setting under any plugin setting or any form metabox tab [\#1718](https://github.com/WordImpress/Give/issues/1718)
- Refactor Core Email Settings [\#1346](https://github.com/WordImpress/Give/issues/1346)
- Enhancing Disconnect User feature on Donor Single View in admin [\#1720](https://github.com/WordImpress/Give/issues/1720)
- Make receipt table row positioning easily customizable [\#1682](https://github.com/WordImpress/Give/issues/1682)
- Investigate possible bug with default terms and conditions [\#1679](https://github.com/WordImpress/Give/issues/1679)
- Show payment gateways label on donation listing page for each donation [\#1674](https://github.com/WordImpress/Give/issues/1674)
- Add date-range to /donation API endpoint [\#758](https://github.com/WordImpress/Give/issues/758)

**Fixed bugs:**

- Categories and Tags are disabled but shown in exported PDF [\#1692](https://github.com/WordImpress/Give/issues/1692)
- Investigate issue with minimum donation amount conflicting with levels [\#1680](https://github.com/WordImpress/Give/issues/1680)
- Add-ons outside of /plugins/ cause page output before headers are sent [\#1672](https://github.com/WordImpress/Give/issues/1672)
- Add missing function give\_get\_currency\_name [\#1670](https://github.com/WordImpress/Give/issues/1670)
- Allow decimal values in amount field [\#1666](https://github.com/WordImpress/Give/issues/1666)
- Wrong currency icon on view donation page [\#1664](https://github.com/WordImpress/Give/issues/1664)
- Donor, Donation, and Donation Form Dropdowns need to be AJAX powered [\#1572](https://github.com/WordImpress/Give/issues/1572)
- Sometimes expiration field in form stops auto formatting date [\#1278](https://github.com/WordImpress/Give/issues/1278)
- Issue/1680 [\#1728](https://github.com/WordImpress/Give/pull/1728) ([DevinWalker](https://github.com/DevinWalker))

**Closed issues:**

- Trunk Folder in Give source code at WordPress Plugin Repository [\#1685](https://github.com/WordImpress/Give/issues/1685)
- Function to clear `give\_stats\_\*` transients created by `get\_earnings` [\#1117](https://github.com/WordImpress/Give/issues/1117)
- Improve Strings and Links to Docs [\#889](https://github.com/WordImpress/Give/issues/889)
- Shorten description strings and link out to documentation [\#876](https://github.com/WordImpress/Give/issues/876)
- Showing Labels for Form Featured Image dropdown [\#1713](https://github.com/WordImpress/Give/issues/1713)
- Conflict with WP101 [\#1710](https://github.com/WordImpress/Give/issues/1710)
- Exports should relocate to Donations \> Tools \> Exports [\#1702](https://github.com/WordImpress/Give/issues/1702)
- Prioritze Donation Form Options metabox while using Yoast SEO plugin [\#1698](https://github.com/WordImpress/Give/issues/1698)
- Minor styling issue in form metabox [\#1695](https://github.com/WordImpress/Give/issues/1695)
- Clicking license notice dismissal shouldn't refresh page [\#1694](https://github.com/WordImpress/Give/issues/1694)
- give\_get\_field\_description\(\) needs to take into account $field\['desc'\] usage [\#1668](https://github.com/WordImpress/Give/issues/1668)
- Metabox Panel styling issue [\#1662](https://github.com/WordImpress/Give/issues/1662)
- Change images on welcome Screen [\#1561](https://github.com/WordImpress/Give/issues/1561)
- Give Workers need to have expanded user roles. [\#844](https://github.com/WordImpress/Give/issues/844)
- When Form Single view is disabled in settings, a "View Form" link shows on Publish/Update [\#646](https://github.com/WordImpress/Give/issues/646)

**Merged pull requests:**

- Release/1.8.8 [\#1749](https://github.com/WordImpress/Give/pull/1749) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1069 [\#1748](https://github.com/WordImpress/Give/pull/1748) ([mehul0810](https://github.com/mehul0810))
- Fix: add filter give\_get\_meta before setting default value [\#1747](https://github.com/WordImpress/Give/pull/1747) ([ravinderk](https://github.com/ravinderk))
- Fix: typo in filter name [\#1745](https://github.com/WordImpress/Give/pull/1745) ([ravinderk](https://github.com/ravinderk))
- Issue/1572 [\#1744](https://github.com/WordImpress/Give/pull/1744) ([DevinWalker](https://github.com/DevinWalker))
- Currency decoding update [\#1743](https://github.com/WordImpress/Give/pull/1743) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1694 [\#1742](https://github.com/WordImpress/Give/pull/1742) ([mehul0810](https://github.com/mehul0810))
- Refactor: goal template and add extra params to filters [\#1741](https://github.com/WordImpress/Give/pull/1741) ([ravinderk](https://github.com/ravinderk))
- Issue/1716 [\#1740](https://github.com/WordImpress/Give/pull/1740) ([mehul0810](https://github.com/mehul0810))
- Deprecated a bunch of \*\_store\_\* and \*\_customer\_\* filters [\#1738](https://github.com/WordImpress/Give/pull/1738) ([DevinWalker](https://github.com/DevinWalker))
- Code updates to PR \#1730 [\#1737](https://github.com/WordImpress/Give/pull/1737) ([DevinWalker](https://github.com/DevinWalker))
- Emgk issue 1720 [\#1736](https://github.com/WordImpress/Give/pull/1736) ([DevinWalker](https://github.com/DevinWalker))
- Mehul0810 issue/1682 1 [\#1735](https://github.com/WordImpress/Give/pull/1735) ([DevinWalker](https://github.com/DevinWalker))
- Change give\_goal\_amount\_funded\_percentage\_output filter position [\#1734](https://github.com/WordImpress/Give/pull/1734) ([ravinderk](https://github.com/ravinderk))
- Hotfix/1692 [\#1733](https://github.com/WordImpress/Give/pull/1733) ([mehul0810](https://github.com/mehul0810))
- Issues/1680 [\#1732](https://github.com/WordImpress/Give/pull/1732) ([ravinderk](https://github.com/ravinderk))
- Hotfix/give get featured image sizes [\#1731](https://github.com/WordImpress/Give/pull/1731) ([ravinderk](https://github.com/ravinderk))
- Fix/ecommerce terminology: Rename give\_checkout\_button\_purchase function [\#1730](https://github.com/WordImpress/Give/pull/1730) ([ravinderk](https://github.com/ravinderk))
- Renamed a bunch of \*\_purchase\_\* functions to \*\_donation\_\* within process-donations.php \#896 [\#1729](https://github.com/WordImpress/Give/pull/1729) ([DevinWalker](https://github.com/DevinWalker))
- Issue/758 [\#1726](https://github.com/WordImpress/Give/pull/1726) ([mehul0810](https://github.com/mehul0810))
- Fix: \#1278 [\#1725](https://github.com/WordImpress/Give/pull/1725) ([ravinderk](https://github.com/ravinderk))
- Issues/1718 [\#1719](https://github.com/WordImpress/Give/pull/1719) ([ravinderk](https://github.com/ravinderk))
- Issue/1713 [\#1717](https://github.com/WordImpress/Give/pull/1717) ([mehul0810](https://github.com/mehul0810))
- Hotfix/graph [\#1714](https://github.com/WordImpress/Give/pull/1714) ([ravinderk](https://github.com/ravinderk))
- issue/1561 [\#1712](https://github.com/WordImpress/Give/pull/1712) ([DevinWalker](https://github.com/DevinWalker))
- Ensure fitvids and scripts for the welcome screen are only output on … [\#1711](https://github.com/WordImpress/Give/pull/1711) ([DevinWalker](https://github.com/DevinWalker))
- refactor give\_currency\_filter [\#1708](https://github.com/WordImpress/Give/pull/1708) ([ravinderk](https://github.com/ravinderk))
- fix phpunit failing test \(version 2.0\) [\#1706](https://github.com/WordImpress/Give/pull/1706) ([ravinderk](https://github.com/ravinderk))
- Fixed - Issue \#1698 [\#1705](https://github.com/WordImpress/Give/pull/1705) ([mehul0810](https://github.com/mehul0810))
- Tabs, not spaces [\#1704](https://github.com/WordImpress/Give/pull/1704) ([corywebb](https://github.com/corywebb))
- Issue/1702 [\#1703](https://github.com/WordImpress/Give/pull/1703) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1672 [\#1699](https://github.com/WordImpress/Give/pull/1699) ([DevinWalker](https://github.com/DevinWalker))
- Expanded Give Worker User Role - Issue/844 [\#1697](https://github.com/WordImpress/Give/pull/1697) ([mehul0810](https://github.com/mehul0810))
- Hotfix/metabox style [\#1696](https://github.com/WordImpress/Give/pull/1696) ([ravinderk](https://github.com/ravinderk))
- Fixed - Issue \#1692 [\#1693](https://github.com/WordImpress/Give/pull/1693) ([mehul0810](https://github.com/mehul0810))
- Hotfix/give currency symbol [\#1691](https://github.com/WordImpress/Give/pull/1691) ([ravinderk](https://github.com/ravinderk))
- Issue/1679 [\#1689](https://github.com/WordImpress/Give/pull/1689) ([DevinWalker](https://github.com/DevinWalker))
- Issues/646 - Form Single View [\#1687](https://github.com/WordImpress/Give/pull/1687) ([mehul0810](https://github.com/mehul0810))
- Add WPML config file [\#1686](https://github.com/WordImpress/Give/pull/1686) ([vukvukovich](https://github.com/vukvukovich))
- textdomain [\#1684](https://github.com/WordImpress/Give/pull/1684) ([sebastienserre](https://github.com/sebastienserre))
- add missing Text-domain l737 [\#1683](https://github.com/WordImpress/Give/pull/1683) ([sebastienserre](https://github.com/sebastienserre))
- issues/1679 [\#1681](https://github.com/WordImpress/Give/pull/1681) ([ravinderk](https://github.com/ravinderk))
- Use give meta related functions [\#1678](https://github.com/WordImpress/Give/pull/1678) ([ravinderk](https://github.com/ravinderk))
- Add post type meta data related functions [\#1676](https://github.com/WordImpress/Give/pull/1676) ([ravinderk](https://github.com/ravinderk))
- Fix: \#1674 [\#1675](https://github.com/WordImpress/Give/pull/1675) ([ravinderk](https://github.com/ravinderk))
- Issues/611 - Email Enhancement [\#1673](https://github.com/WordImpress/Give/pull/1673) ([ravinderk](https://github.com/ravinderk))
- Fix: add missing fx give\_get\_currency\_name [\#1671](https://github.com/WordImpress/Give/pull/1671) ([ravinderk](https://github.com/ravinderk))
- Added additional conditional check for $field\[‘desc’\] \#1668 [\#1669](https://github.com/WordImpress/Give/pull/1669) ([DevinWalker](https://github.com/DevinWalker))
- issues/1666 [\#1667](https://github.com/WordImpress/Give/pull/1667) ([ravinderk](https://github.com/ravinderk))
- issues/1664 [\#1665](https://github.com/WordImpress/Give/pull/1665) ([ravinderk](https://github.com/ravinderk))
- issues/1662 [\#1663](https://github.com/WordImpress/Give/pull/1663) ([ravinderk](https://github.com/ravinderk))

## [1.8.7.1](https://github.com/WordImpress/Give/tree/1.8.7.1) (2017-05-05)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.7...1.8.7.1)

**Fixed bugs:**

- License notice returns and is not dismissible when expiration timestamp is out of date [\#1658](https://github.com/WordImpress/Give/issues/1658)

**Closed issues:**

- PHP Strict Standards warning displays when you have add-ons installed [\#1659](https://github.com/WordImpress/Give/issues/1659)

**Merged pull requests:**

- Issues/1658 [\#1661](https://github.com/WordImpress/Give/pull/1661) ([ravinderk](https://github.com/ravinderk))
- Fix: \#1659 [\#1660](https://github.com/WordImpress/Give/pull/1660) ([ravinderk](https://github.com/ravinderk))

## [1.8.7](https://github.com/WordImpress/Give/tree/1.8.7) (2017-05-04)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.6...1.8.7)

**Implemented enhancements:**

- Add "processing" donation payment status into core [\#1615](https://github.com/WordImpress/Give/issues/1615)
- Licensing Improvements Part II [\#1586](https://github.com/WordImpress/Give/issues/1586)

**Fixed bugs:**

- Clearing Test Data should not affect non-test data  [\#1654](https://github.com/WordImpress/Give/issues/1654)
- When an add-on license is deactivated it should be remove from field and database [\#1649](https://github.com/WordImpress/Give/issues/1649)
- Donation form model rendering issue [\#1636](https://github.com/WordImpress/Give/issues/1636)
- Ensure core version updates run in a proper order  [\#1626](https://github.com/WordImpress/Give/issues/1626)
- give\_stats\_\* transients filling up database unnecessarily - New Give\_Cache API needed [\#1617](https://github.com/WordImpress/Give/issues/1617)
- New User notification emails formatting is broken. [\#1577](https://github.com/WordImpress/Give/issues/1577)

**Closed issues:**

- After Give + addons update, custom form fields are misplaced. [\#1645](https://github.com/WordImpress/Give/issues/1645)
- User request: Implement Express checkout for PayPay Standard [\#1629](https://github.com/WordImpress/Give/issues/1629)
- can't extend statuses [\#1606](https://github.com/WordImpress/Give/issues/1606)
- Wording issues [\#1653](https://github.com/WordImpress/Give/issues/1653)
- If a license is not active for an add-on display notice on plugins page [\#1648](https://github.com/WordImpress/Give/issues/1648)
- Update WP Session Manager version included [\#1646](https://github.com/WordImpress/Give/issues/1646)
- Typos in Give License handler [\#1638](https://github.com/WordImpress/Give/issues/1638)
- Danish Kroner is formatted incorrectly [\#1632](https://github.com/WordImpress/Give/issues/1632)
- Make "Email already in use" error more explanatory. [\#1624](https://github.com/WordImpress/Give/issues/1624)
- Make New User Notification email filterable [\#1623](https://github.com/WordImpress/Give/issues/1623)
- Change the "placeholder" address for offline donations [\#1620](https://github.com/WordImpress/Give/issues/1620)
- Shortcodes should not echo output [\#1614](https://github.com/WordImpress/Give/issues/1614)
- Modifications to the Admin Notification for an offline notification. [\#1569](https://github.com/WordImpress/Give/issues/1569)
- Use @property and @property-read PHPdocs [\#992](https://github.com/WordImpress/Give/issues/992)

**Merged pull requests:**

- Release/1.8.7 [\#1657](https://github.com/WordImpress/Give/pull/1657) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1653 [\#1656](https://github.com/WordImpress/Give/pull/1656) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1654 [\#1655](https://github.com/WordImpress/Give/pull/1655) ([DevinWalker](https://github.com/DevinWalker))
- issues/1649-devin-updates-fixes [\#1652](https://github.com/WordImpress/Give/pull/1652) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1649 [\#1650](https://github.com/WordImpress/Give/pull/1650) ([ravinderk](https://github.com/ravinderk))
- Issue/1646 [\#1647](https://github.com/WordImpress/Give/pull/1647) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1615 [\#1644](https://github.com/WordImpress/Give/pull/1644) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1624 [\#1643](https://github.com/WordImpress/Give/pull/1643) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1614 [\#1642](https://github.com/WordImpress/Give/pull/1642) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1620 [\#1641](https://github.com/WordImpress/Give/pull/1641) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1632 [\#1640](https://github.com/WordImpress/Give/pull/1640) ([DevinWalker](https://github.com/DevinWalker))
- typo fixes [\#1639](https://github.com/WordImpress/Give/pull/1639) ([Benunc](https://github.com/Benunc))
- Issues/1636 [\#1637](https://github.com/WordImpress/Give/pull/1637) ([ravinderk](https://github.com/ravinderk))
- Add give\_init action hook [\#1635](https://github.com/WordImpress/Give/pull/1635) ([ravinderk](https://github.com/ravinderk))
- feature/version\_update [\#1634](https://github.com/WordImpress/Give/pull/1634) ([ravinderk](https://github.com/ravinderk))
- Issue/1569 [\#1633](https://github.com/WordImpress/Give/pull/1633) ([DevinWalker](https://github.com/DevinWalker))
- Fix: \#1626 [\#1628](https://github.com/WordImpress/Give/pull/1628) ([ravinderk](https://github.com/ravinderk))
- Implement Give\_Cache for all transients [\#1627](https://github.com/WordImpress/Give/pull/1627) ([ravinderk](https://github.com/ravinderk))
- Issues/1617 [\#1622](https://github.com/WordImpress/Give/pull/1622) ([ravinderk](https://github.com/ravinderk))
- Issues/1577 [\#1621](https://github.com/WordImpress/Give/pull/1621) ([ravinderk](https://github.com/ravinderk))
- Issues/1586 [\#1619](https://github.com/WordImpress/Give/pull/1619) ([ravinderk](https://github.com/ravinderk))
- Issue/992 [\#1613](https://github.com/WordImpress/Give/pull/1613) ([DevinWalker](https://github.com/DevinWalker))

## [1.8.6](https://github.com/WordImpress/Give/tree/1.8.6) (2017-04-10)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.5...1.8.6)

**Implemented enhancements:**

- Payment Gateway API Keys and sensitive fields should be a password field [\#1455](https://github.com/WordImpress/Give/issues/1455)

**Fixed bugs:**

- Prevent "Show Terms" click event from running multiple times [\#1602](https://github.com/WordImpress/Give/issues/1602)
- Wrong label applied on Donation Confirmation and Email Receipt when Donating a Custom Amount on Multi-level forms [\#1598](https://github.com/WordImpress/Give/issues/1598)
- Multilevel Form issues with 10+ options. [\#1592](https://github.com/WordImpress/Give/issues/1592)

**Closed issues:**

- Rendering two give forms shortcodes on the same page results in duplicated IDs and invalid code [\#1605](https://github.com/WordImpress/Give/issues/1605)
- The plugin outputs HTML that does not pass validation [\#1604](https://github.com/WordImpress/Give/issues/1604)
- FEATURE/ADDON REQUEST. Kiosk mode + Incorporating with a card swiper/chip reader. [\#1594](https://github.com/WordImpress/Give/issues/1594)
- Uncaught TypeError: jQuery\(…\).qtip is not a function [\#1591](https://github.com/WordImpress/Give/issues/1591)
- Form doesn't change when user selects payment method [\#1590](https://github.com/WordImpress/Give/issues/1590)
- Give API not sending Billing Fields [\#1587](https://github.com/WordImpress/Give/issues/1587)
- Give Plugin Breaks Eform Plugin Quizes [\#1584](https://github.com/WordImpress/Give/issues/1584)
- Add slack channel invite link to plugin about page [\#1388](https://github.com/WordImpress/Give/issues/1388)
- give\_get\_current\_page\_url\(\) should really trailing slash it [\#1589](https://github.com/WordImpress/Give/issues/1589)
- Remove give\_export\_all\_customers\(\) - no longer in use [\#1571](https://github.com/WordImpress/Give/issues/1571)
- Lingering bugs with Settings tab dropdown [\#1544](https://github.com/WordImpress/Give/issues/1544)

**Merged pull requests:**

- Issue/1544 [\#1611](https://github.com/WordImpress/Give/pull/1611) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.8.6 [\#1610](https://github.com/WordImpress/Give/pull/1610) ([DevinWalker](https://github.com/DevinWalker))
- Fix unit test failing in PHP 7.1 [\#1608](https://github.com/WordImpress/Give/pull/1608) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1602 [\#1603](https://github.com/WordImpress/Give/pull/1603) ([ravinderk](https://github.com/ravinderk))
- Issue/1598 [\#1599](https://github.com/WordImpress/Give/pull/1599) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1589 [\#1597](https://github.com/WordImpress/Give/pull/1597) ([DevinWalker](https://github.com/DevinWalker))
- Removed give\_export\_all\_customers\(\) and code cleanup  [\#1595](https://github.com/WordImpress/Give/pull/1595) ([DevinWalker](https://github.com/DevinWalker))
- fix \#1592 [\#1593](https://github.com/WordImpress/Give/pull/1593) ([ravinderk](https://github.com/ravinderk))
- Update Documentation. [\#1588](https://github.com/WordImpress/Give/pull/1588) ([MaedahBatool](https://github.com/MaedahBatool))

## [1.8.5](https://github.com/WordImpress/Give/tree/1.8.5) (2017-03-16)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.4...1.8.5)

**Implemented enhancements:**

- Support Posting data to Give via the Give API [\#1576](https://github.com/WordImpress/Give/issues/1576)
- Add Billing Fields as an option in PayPal Standard [\#1573](https://github.com/WordImpress/Give/issues/1573)
- Add colorpicker setting field to admin setting page API [\#1566](https://github.com/WordImpress/Give/issues/1566)
- Button display mode should have attribute for Button Text [\#1494](https://github.com/WordImpress/Give/issues/1494)

**Fixed bugs:**

- Upgrade routine cannot run on plugins listing page due to JS error [\#1580](https://github.com/WordImpress/Give/issues/1580)
- 1.8 Upgrade routine is incorrectly disabling Offline Donations gateway per form [\#1579](https://github.com/WordImpress/Give/issues/1579)
- Add backwards compatibility for those who haven't run upgrade for give\_logged\_in\_only [\#1578](https://github.com/WordImpress/Give/issues/1578)

**Merged pull requests:**

- New option to toggle billing details and corresponding logic \#1573 [\#1583](https://github.com/WordImpress/Give/pull/1583) ([DevinWalker](https://github.com/DevinWalker))
- Fixes for \#1579 and \#1580 [\#1582](https://github.com/WordImpress/Give/pull/1582) ([DevinWalker](https://github.com/DevinWalker))
- Updated give\_logged\_in\_only to return enabled if metakey missing [\#1581](https://github.com/WordImpress/Give/pull/1581) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1494 [\#1568](https://github.com/WordImpress/Give/pull/1568) ([ravinderk](https://github.com/ravinderk))
- Add colorpicker setting field [\#1567](https://github.com/WordImpress/Give/pull/1567) ([ravinderk](https://github.com/ravinderk))
- Issue/1494 [\#1564](https://github.com/WordImpress/Give/pull/1564) ([DevinWalker](https://github.com/DevinWalker))
- Fix \#1494 [\#1563](https://github.com/WordImpress/Give/pull/1563) ([ravinderk](https://github.com/ravinderk))
- Fix \#1544 [\#1562](https://github.com/WordImpress/Give/pull/1562) ([ravinderk](https://github.com/ravinderk))

## [1.8.4](https://github.com/WordImpress/Give/tree/1.8.4) (2017-03-01)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.3...1.8.4)

**Fixed bugs:**

- Update software licensing class and fix Active licenses placeholders  [\#1556](https://github.com/WordImpress/Give/issues/1556)
- Registration + Login is not working when guest donation is enabled on 1.8.3 [\#1553](https://github.com/WordImpress/Give/issues/1553)
- RLT with banner and alert [\#1547](https://github.com/WordImpress/Give/issues/1547)

**Merged pull requests:**

- Release/1.8.4 [\#1560](https://github.com/WordImpress/Give/pull/1560) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1547 [\#1559](https://github.com/WordImpress/Give/pull/1559) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1556 [\#1558](https://github.com/WordImpress/Give/pull/1558) ([ravinderk](https://github.com/ravinderk))
- Update EDD\_SL\_Plugin\_Updater.php file to the latest version \#1556 [\#1557](https://github.com/WordImpress/Give/pull/1557) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1553 [\#1555](https://github.com/WordImpress/Give/pull/1555) ([ravinderk](https://github.com/ravinderk))

## [1.8.3](https://github.com/WordImpress/Give/tree/1.8.3) (2017-02-27)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.2...1.8.3)

**Implemented enhancements:**

- Settings API: New API Key Field Needed  [\#1540](https://github.com/WordImpress/Give/issues/1540)
- Close form message upon Goal Complete should be a WYSIWYG instead of Give alert [\#1504](https://github.com/WordImpress/Give/issues/1504)

**Fixed bugs:**

- Email Access issues w/ 1.8+ [\#1551](https://github.com/WordImpress/Give/issues/1551)
- Hyphen in icomoon fonts triggers security warnings [\#1535](https://github.com/WordImpress/Give/issues/1535)
- Show correct logs count for payment errors list [\#1531](https://github.com/WordImpress/Give/issues/1531)
- Gateway and  Donation ID is not showing in logs [\#1529](https://github.com/WordImpress/Give/issues/1529)

**Closed issues:**

- The new Goal Complete WYSIWYG doesn't currently render oEmbeds [\#1545](https://github.com/WordImpress/Give/issues/1545)
- Settings pages return errors w/ Hebrew [\#1510](https://github.com/WordImpress/Give/issues/1510)

**Merged pull requests:**

- Issue/1551 [\#1552](https://github.com/WordImpress/Give/pull/1552) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Release/1.8.3 [\#1550](https://github.com/WordImpress/Give/pull/1550) ([DevinWalker](https://github.com/DevinWalker))
- Use `the\_content` to output Goal complete message \#1545 [\#1549](https://github.com/WordImpress/Give/pull/1549) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1544 [\#1548](https://github.com/WordImpress/Give/pull/1548) ([ravinderk](https://github.com/ravinderk))
- Fix translation related cmb2 compability issue [\#1546](https://github.com/WordImpress/Give/pull/1546) ([ravinderk](https://github.com/ravinderk))
- Removed hyphens flagging mod\_security regex \#1535 [\#1543](https://github.com/WordImpress/Give/pull/1543) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1510 [\#1542](https://github.com/WordImpress/Give/pull/1542) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1540 [\#1541](https://github.com/WordImpress/Give/pull/1541) ([DevinWalker](https://github.com/DevinWalker))
- Fix cmb2 compability issue with php7 [\#1537](https://github.com/WordImpress/Give/pull/1537) ([ravinderk](https://github.com/ravinderk))
- Hotfix/setup\_transaction\_id [\#1534](https://github.com/WordImpress/Give/pull/1534) ([ravinderk](https://github.com/ravinderk))
- Show unnamed title [\#1533](https://github.com/WordImpress/Give/pull/1533) ([ravinderk](https://github.com/ravinderk))
- Issues/1531 [\#1532](https://github.com/WordImpress/Give/pull/1532) ([ravinderk](https://github.com/ravinderk))
- Show correct donation id and gateway name in log list [\#1530](https://github.com/WordImpress/Give/pull/1530) ([ravinderk](https://github.com/ravinderk))

## [1.8.2](https://github.com/WordImpress/Give/tree/1.8.2) (2017-02-21)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8.1...1.8.2)

**Implemented enhancements:**

- Offline Donations Email field should have Email Tags listed [\#1516](https://github.com/WordImpress/Give/issues/1516)
- Form Metabox API should have the ability to set a Give icon or Dashicon class name [\#1506](https://github.com/WordImpress/Give/issues/1506)

**Fixed bugs:**

- Reporting "Last Year" filter shows December donations on wrong vertical Axis [\#1428](https://github.com/WordImpress/Give/issues/1428)
- Weird WordPress editor height in metabox setting fields [\#1522](https://github.com/WordImpress/Give/issues/1522)
- Offline donation instructions Per form is not functional on 1.8.1 [\#1513](https://github.com/WordImpress/Give/issues/1513)
- Floating labels shouldn't increase the input height [\#1511](https://github.com/WordImpress/Give/issues/1511)
- Multi-level donation total does not always reflect current selection [\#1502](https://github.com/WordImpress/Give/issues/1502)
- Donate button does not reappear if login is cancelled [\#1482](https://github.com/WordImpress/Give/issues/1482)

**Closed issues:**

- show login form non logged in donor in history page shortcode [\#1485](https://github.com/WordImpress/Give/issues/1485)

**Merged pull requests:**

- Release/1.8.2 [\#1528](https://github.com/WordImpress/Give/pull/1528) ([DevinWalker](https://github.com/DevinWalker))
- Hotfix/wpeditor height [\#1523](https://github.com/WordImpress/Give/pull/1523) ([ravinderk](https://github.com/ravinderk))
- Display \[give\_login\] shortcode to prompt user to login to view donati… [\#1521](https://github.com/WordImpress/Give/pull/1521) ([DevinWalker](https://github.com/DevinWalker))
- issue/1482 [\#1520](https://github.com/WordImpress/Give/pull/1520) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1511 [\#1519](https://github.com/WordImpress/Give/pull/1519) ([DevinWalker](https://github.com/DevinWalker))
- Output email template tags for offline donations gate \#1516 [\#1518](https://github.com/WordImpress/Give/pull/1518) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1513 [\#1514](https://github.com/WordImpress/Give/pull/1514) ([ravinderk](https://github.com/ravinderk))
- Issues/1506 [\#1509](https://github.com/WordImpress/Give/pull/1509) ([ravinderk](https://github.com/ravinderk))
- Issue/896 [\#1507](https://github.com/WordImpress/Give/pull/1507) ([DevinWalker](https://github.com/DevinWalker))
- Fix \#1502 [\#1503](https://github.com/WordImpress/Give/pull/1503) ([ravinderk](https://github.com/ravinderk))

## [1.8.1](https://github.com/WordImpress/Give/tree/1.8.1) (2017-02-15)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.8...1.8.1)

**Fixed bugs:**

- "Give in Footer" setting still looking for 'on' rather than new 'enabled' [\#1498](https://github.com/WordImpress/Give/issues/1498)

**Merged pull requests:**

- Release/1.8.1 [\#1501](https://github.com/WordImpress/Give/pull/1501) ([DevinWalker](https://github.com/DevinWalker))
- Hotfix/is single price mode [\#1500](https://github.com/WordImpress/Give/pull/1500) ([ravinderk](https://github.com/ravinderk))
- Issues/1498 [\#1499](https://github.com/WordImpress/Give/pull/1499) ([ravinderk](https://github.com/ravinderk))

## [1.8](https://github.com/WordImpress/Give/tree/1.8) (2017-02-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.7.2...1.8)

**Implemented enhancements:**

- New Metabox API data-type=decimal/currency needs to trigger formatting when not focused [\#1460](https://github.com/WordImpress/Give/issues/1460)
- Render Donations -\> Reports admin page with new setting api [\#1478](https://github.com/WordImpress/Give/issues/1478)
- Clean up form styles [\#1453](https://github.com/WordImpress/Give/issues/1453)
- New Metabox API needs an Upload field [\#1451](https://github.com/WordImpress/Give/issues/1451)
- Add Upgrade Notice for Version 1.8 db changes [\#1408](https://github.com/WordImpress/Give/issues/1408)
- Implement shortlinks in all code references [\#1405](https://github.com/WordImpress/Give/issues/1405)
- Update getting started page  images and gifs for release 1.8 [\#1264](https://github.com/WordImpress/Give/issues/1264)
- Ensure the repeater field is available for developers for single and multiple fields in API [\#1171](https://github.com/WordImpress/Give/issues/1171)
- New "Tools" submenu [\#1046](https://github.com/WordImpress/Give/issues/1046)
- Only show activation banner for user who activated the plugin [\#1036](https://github.com/WordImpress/Give/issues/1036)
- Change some settings to properly reflect Enable/Disable Correctly [\#962](https://github.com/WordImpress/Give/issues/962)
- Auto fill total donation amount when admin user change donation level on transaction edit screen [\#884](https://github.com/WordImpress/Give/issues/884)
- Provide the ability to export donations based on category or tag [\#867](https://github.com/WordImpress/Give/issues/867)
- Bulk deleting transactions does not display a notification  [\#850](https://github.com/WordImpress/Give/issues/850)
- Add more context to system info page [\#826](https://github.com/WordImpress/Give/issues/826)
- Create Global Terms and Conditions setting [\#679](https://github.com/WordImpress/Give/issues/679)
- Refactor the Give Settings section [\#668](https://github.com/WordImpress/Give/issues/668)
- New Shortcode attribute: button\_only=true [\#520](https://github.com/WordImpress/Give/issues/520)
- a11y Review List [\#325](https://github.com/WordImpress/Give/issues/325)
- Design and develop new donation form creation UI [\#281](https://github.com/WordImpress/Give/issues/281)
- Change reCAPTCHA to shortlink for easier redirect in the future [\#1402](https://github.com/WordImpress/Give/pull/1402) ([mathetos](https://github.com/mathetos))

**Fixed bugs:**

- Dynamic Error Messages are not internationalized [\#1394](https://github.com/WordImpress/Give/issues/1394)
- Show correct donor count on donor listing page [\#1497](https://github.com/WordImpress/Give/issues/1497)
- Donor search is not working on Donations -\> Reports -\> Donors for donor listing [\#1486](https://github.com/WordImpress/Give/issues/1486)
- Show offline payment gateways on form edit screen for new form [\#1479](https://github.com/WordImpress/Give/issues/1479)
- Minor CSS Issue with Highlighted Tabs  [\#1444](https://github.com/WordImpress/Give/issues/1444)
- Shortcode parameters for Button display broken in Give 1.8 [\#1435](https://github.com/WordImpress/Give/issues/1435)
- Offline Donation Instructions email is blank by default in Give 1.8 [\#1434](https://github.com/WordImpress/Give/issues/1434)
- New Give Receipt shortcode attribute "status\_notice" doesn't work as expected [\#1431](https://github.com/WordImpress/Give/issues/1431)
- Shortcode parameters broken in 1.8 [\#1430](https://github.com/WordImpress/Give/issues/1430)
- Offline Donation Confirmation page not updating after donation marked as Complete [\#1429](https://github.com/WordImpress/Give/issues/1429)
- "Export Donors" creating multiple instances of the same donor. [\#1426](https://github.com/WordImpress/Give/issues/1426)
- Form Action Not Updating on Payment Gateway Change [\#1418](https://github.com/WordImpress/Give/issues/1418)
- "Create New Donor" Link not working on admin. [\#1417](https://github.com/WordImpress/Give/issues/1417)
- Member-only form problem has resurfaced in 1.8 [\#1398](https://github.com/WordImpress/Give/issues/1398)
- Incompatibility with PHP 7.1 [\#1377](https://github.com/WordImpress/Give/issues/1377)
- Release/1.8 WYSIWYG editor format saving issue [\#1311](https://github.com/WordImpress/Give/issues/1311)
- Add currency symbol for price setting field [\#1299](https://github.com/WordImpress/Give/issues/1299)
- Legend are not showing properly in chrome browser [\#1298](https://github.com/WordImpress/Give/issues/1298)
- Show default title for untitled form in donation list. [\#1276](https://github.com/WordImpress/Give/issues/1276)
- If only one payment gateway is enabled than make sure that default gateway should set properly [\#1268](https://github.com/WordImpress/Give/issues/1268)
- Clean up Donor search results URLs [\#1175](https://github.com/WordImpress/Give/issues/1175)
- Add support for CMB2 custom fields [\#1166](https://github.com/WordImpress/Give/issues/1166)
- Tools \> API issues with generating key and link to view api log [\#1073](https://github.com/WordImpress/Give/issues/1073)
- Setting "Success" and "Failed" pages to the same page results in Failed status with PayPal Standard [\#724](https://github.com/WordImpress/Give/issues/724)

**Closed issues:**

- Loading spinner does not disappear when switching gateways [\#1495](https://github.com/WordImpress/Give/issues/1495)
- Add a way to display custom fields to the bottom of the donation receipt shortcode output [\#1406](https://github.com/WordImpress/Give/issues/1406)
- Invalid form control JS warning -  'Donate Now' not functioning [\#1373](https://github.com/WordImpress/Give/issues/1373)
- PHP Notice and Catchable fatal error [\#1372](https://github.com/WordImpress/Give/issues/1372)
- Review all labels, settings, and descriptions for grammer [\#1488](https://github.com/WordImpress/Give/issues/1488)
- 1.8 update issue for fields that are "Disable" checkboxes [\#1470](https://github.com/WordImpress/Give/issues/1470)
- Implement shortlinks in readme.txt and readme.md [\#1467](https://github.com/WordImpress/Give/issues/1467)
- Improve responsive single donation form tabs [\#1466](https://github.com/WordImpress/Give/issues/1466)
- Typo "your" in readme.txt [\#1454](https://github.com/WordImpress/Give/issues/1454)
- Link Donor's Name to Donor Profile on Donor's Report Listing Screen [\#1448](https://github.com/WordImpress/Give/issues/1448)
- CSS Tweak for Single Form Edit Icons and Text Spacing in 1.8 [\#1441](https://github.com/WordImpress/Give/issues/1441)
- Add support for multi-level arg donations within give\_send\_back\_to\_checkout [\#1422](https://github.com/WordImpress/Give/issues/1422)
- Settings tab UI improvement to prevent bumping to two lines [\#1413](https://github.com/WordImpress/Give/issues/1413)
- Minor typo in translation banner [\#1410](https://github.com/WordImpress/Give/issues/1410)
- Display horizontal rule below subtabs and display single tab if present [\#1409](https://github.com/WordImpress/Give/issues/1409)
- Guest Donations description adds confusion to what the setting does [\#1397](https://github.com/WordImpress/Give/issues/1397)
- If "Offline Donations" is disabled, don't display the tab on the single donation edit screen [\#1391](https://github.com/WordImpress/Give/issues/1391)
- Settings table rows need compatibility with CMB2 row\_classes option [\#1370](https://github.com/WordImpress/Give/issues/1370)
- Convert "Logs" dropdown select to tabs to keep UI consistent [\#1368](https://github.com/WordImpress/Give/issues/1368)
- Reorder request for donation form tabs  [\#1309](https://github.com/WordImpress/Give/issues/1309)
- Add display settings to widget [\#1269](https://github.com/WordImpress/Give/issues/1269)
- Add Download option as DEFAULT for System Info [\#1260](https://github.com/WordImpress/Give/issues/1260)
- Improve CSS when background colors present [\#1258](https://github.com/WordImpress/Give/issues/1258)
- If only one payment gateway is enabled the "Select Payment Method" fieldset should be hidden  [\#1122](https://github.com/WordImpress/Give/issues/1122)
- Reports UI / UX Improvements [\#1114](https://github.com/WordImpress/Give/issues/1114)
- Add default icon for add-on tabs within donation form creation admin UI [\#1078](https://github.com/WordImpress/Give/issues/1078)
- Settle on "Enable/Disable" rather than "Yes/No" [\#1065](https://github.com/WordImpress/Give/issues/1065)
- New Settings and Form Fields Fixes & Optimization [\#1064](https://github.com/WordImpress/Give/issues/1064)
- Update Settings field ids for improved code clarity [\#1063](https://github.com/WordImpress/Give/issues/1063)
- Ensure settings subfields toggle properly [\#1062](https://github.com/WordImpress/Give/issues/1062)
- All setting notices should be dismissible [\#1061](https://github.com/WordImpress/Give/issues/1061)
- Deprecate usage of CMB2 in favor of our own settings & metabox fields [\#991](https://github.com/WordImpress/Give/issues/991)
- Create settings update script for settings changes [\#976](https://github.com/WordImpress/Give/issues/976)
- Convert all Doc links to Shortened URLs for better management [\#890](https://github.com/WordImpress/Give/issues/890)
- Include the Icomoon JSON file in Github repo [\#795](https://github.com/WordImpress/Give/issues/795)

**Merged pull requests:**

- Issues/1486 [\#1496](https://github.com/WordImpress/Give/pull/1496) ([ravinderk](https://github.com/ravinderk))
- Release/1.8 [\#1492](https://github.com/WordImpress/Give/pull/1492) ([DevinWalker](https://github.com/DevinWalker))
- Implement shorturls to readme.txt [\#1491](https://github.com/WordImpress/Give/pull/1491) ([mathetos](https://github.com/mathetos))
- Issue/1488 [\#1490](https://github.com/WordImpress/Give/pull/1490) ([mathetos](https://github.com/mathetos))
- Issue/1453 [\#1489](https://github.com/WordImpress/Give/pull/1489) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1478 [\#1487](https://github.com/WordImpress/Give/pull/1487) ([ravinderk](https://github.com/ravinderk))
- Do not use global params [\#1483](https://github.com/WordImpress/Give/pull/1483) ([ravinderk](https://github.com/ravinderk))
- Show offline payment gateway for new donation form [\#1480](https://github.com/WordImpress/Give/pull/1480) ([ravinderk](https://github.com/ravinderk))
- Make setting tab responsive for all setting pages [\#1477](https://github.com/WordImpress/Give/pull/1477) ([ravinderk](https://github.com/ravinderk))
- Issue/1453 [\#1476](https://github.com/WordImpress/Give/pull/1476) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1470 [\#1471](https://github.com/WordImpress/Give/pull/1471) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1466 [\#1468](https://github.com/WordImpress/Give/pull/1468) ([DevinWalker](https://github.com/DevinWalker))
- readme.txt updates fixed typos, improved language [\#1465](https://github.com/WordImpress/Give/pull/1465) ([DevinWalker](https://github.com/DevinWalker))
- Minor Fix: Set default currency position [\#1464](https://github.com/WordImpress/Give/pull/1464) ([ravinderk](https://github.com/ravinderk))
- Minor Fix: style registration [\#1463](https://github.com/WordImpress/Give/pull/1463) ([ravinderk](https://github.com/ravinderk))
- Issues/1408 [\#1462](https://github.com/WordImpress/Give/pull/1462) ([ravinderk](https://github.com/ravinderk))
- makes the language less confusing [\#1461](https://github.com/WordImpress/Give/pull/1461) ([Benunc](https://github.com/Benunc))
- Issues/1413 [\#1459](https://github.com/WordImpress/Give/pull/1459) ([ravinderk](https://github.com/ravinderk))
- Issues/1451 [\#1458](https://github.com/WordImpress/Give/pull/1458) ([ravinderk](https://github.com/ravinderk))
- Issue/1453 [\#1456](https://github.com/WordImpress/Give/pull/1456) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1422 [\#1450](https://github.com/WordImpress/Give/pull/1450) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1444 [\#1449](https://github.com/WordImpress/Give/pull/1449) ([DevinWalker](https://github.com/DevinWalker))
- Fix show/hide tab process lagging [\#1447](https://github.com/WordImpress/Give/pull/1447) ([ravinderk](https://github.com/ravinderk))
- Issues/1426 [\#1446](https://github.com/WordImpress/Give/pull/1446) ([ravinderk](https://github.com/ravinderk))
- Issue/1444 [\#1445](https://github.com/WordImpress/Give/pull/1445) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1441 [\#1442](https://github.com/WordImpress/Give/pull/1442) ([DevinWalker](https://github.com/DevinWalker))
- Do not always show form title for button display style [\#1440](https://github.com/WordImpress/Give/pull/1440) ([ravinderk](https://github.com/ravinderk))
- Issues/1429 [\#1439](https://github.com/WordImpress/Give/pull/1439) ([ravinderk](https://github.com/ravinderk))
- Setup default offline email content on plugin install [\#1438](https://github.com/WordImpress/Give/pull/1438) ([ravinderk](https://github.com/ravinderk))
- Minor Fixes [\#1436](https://github.com/WordImpress/Give/pull/1436) ([ravinderk](https://github.com/ravinderk))
- Issue/1430 [\#1433](https://github.com/WordImpress/Give/pull/1433) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1431 [\#1432](https://github.com/WordImpress/Give/pull/1432) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Add give\_form\_validation\_passed javascript event [\#1421](https://github.com/WordImpress/Give/pull/1421) ([ravinderk](https://github.com/ravinderk))
- Fix issues \#1417 [\#1420](https://github.com/WordImpress/Give/pull/1420) ([ravinderk](https://github.com/ravinderk))
- Update donation form action url [\#1419](https://github.com/WordImpress/Give/pull/1419) ([ravinderk](https://github.com/ravinderk))
- Hotfix/r18 [\#1416](https://github.com/WordImpress/Give/pull/1416) ([ravinderk](https://github.com/ravinderk))
- Issues/1413 [\#1415](https://github.com/WordImpress/Give/pull/1415) ([ravinderk](https://github.com/ravinderk))
- Issues/1409 [\#1412](https://github.com/WordImpress/Give/pull/1412) ([ravinderk](https://github.com/ravinderk))
- Fixed minor typo \#1410 [\#1411](https://github.com/WordImpress/Give/pull/1411) ([DevinWalker](https://github.com/DevinWalker))
- Resolves \#1405 [\#1407](https://github.com/WordImpress/Give/pull/1407) ([mathetos](https://github.com/mathetos))
- Change System Info link to a short url [\#1404](https://github.com/WordImpress/Give/pull/1404) ([mathetos](https://github.com/mathetos))
- Changing the Forms Terms shortlink  [\#1403](https://github.com/WordImpress/Give/pull/1403) ([mathetos](https://github.com/mathetos))
- Issues/1398 [\#1401](https://github.com/WordImpress/Give/pull/1401) ([ravinderk](https://github.com/ravinderk))
- Typo in Terms and Conditions Settings page. [\#1399](https://github.com/WordImpress/Give/pull/1399) ([mathetos](https://github.com/mathetos))
- Fixes \#1394 [\#1395](https://github.com/WordImpress/Give/pull/1395) ([mathetos](https://github.com/mathetos))
- Fix \#1175 [\#1393](https://github.com/WordImpress/Give/pull/1393) ([ravinderk](https://github.com/ravinderk))
- Fix \#1391 [\#1392](https://github.com/WordImpress/Give/pull/1392) ([ravinderk](https://github.com/ravinderk))
- Fix version comperision [\#1389](https://github.com/WordImpress/Give/pull/1389) ([ravinderk](https://github.com/ravinderk))
- Highlight tab which has submenu when active [\#1387](https://github.com/WordImpress/Give/pull/1387) ([ravinderk](https://github.com/ravinderk))
- Update url in form table to show donation logs for specific form [\#1386](https://github.com/WordImpress/Give/pull/1386) ([ravinderk](https://github.com/ravinderk))
- Hotfix/setting api updates [\#1385](https://github.com/WordImpress/Give/pull/1385) ([ravinderk](https://github.com/ravinderk))
- Feature/subtab metabox [\#1383](https://github.com/WordImpress/Give/pull/1383) ([ravinderk](https://github.com/ravinderk))
- Hotfix/fix notices [\#1381](https://github.com/WordImpress/Give/pull/1381) ([ravinderk](https://github.com/ravinderk))
- Remove CBM2 [\#1379](https://github.com/WordImpress/Give/pull/1379) ([ravinderk](https://github.com/ravinderk))
- Set all docs urls to HTTP instead of HTTPS [\#1375](https://github.com/WordImpress/Give/pull/1375) ([mathetos](https://github.com/mathetos))
- Minor fix [\#1374](https://github.com/WordImpress/Give/pull/1374) ([ravinderk](https://github.com/ravinderk))
- Issue/1370 [\#1371](https://github.com/WordImpress/Give/pull/1371) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1368 [\#1369](https://github.com/WordImpress/Give/pull/1369) ([ravinderk](https://github.com/ravinderk))
- Minor Fix [\#1367](https://github.com/WordImpress/Give/pull/1367) ([ravinderk](https://github.com/ravinderk))
- Minor Fix [\#1366](https://github.com/WordImpress/Give/pull/1366) ([ravinderk](https://github.com/ravinderk))
- Fix notice [\#1365](https://github.com/WordImpress/Give/pull/1365) ([ravinderk](https://github.com/ravinderk))
- issues/1122 [\#1364](https://github.com/WordImpress/Give/pull/1364) ([ravinderk](https://github.com/ravinderk))
- Add links to documentation throughout the plugin settings [\#1363](https://github.com/WordImpress/Give/pull/1363) ([mathetos](https://github.com/mathetos))
- Issues/281 [\#1053](https://github.com/WordImpress/Give/pull/1053) ([ravinderk](https://github.com/ravinderk))
- Issues/668 [\#1039](https://github.com/WordImpress/Give/pull/1039) ([ravinderk](https://github.com/ravinderk))

## [1.7.2](https://github.com/WordImpress/Give/tree/1.7.2) (2016-12-21)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.7.1...1.7.2)

**Fixed bugs:**

- Form Preview doesn't work while in Draft Status [\#1343](https://github.com/WordImpress/Give/issues/1343)

**Closed issues:**

- Recurring donation email receipts should say they are a recurring donation  [\#1348](https://github.com/WordImpress/Give/issues/1348)
- New User email producing issues.  [\#1215](https://github.com/WordImpress/Give/issues/1215)
- Compatibility with TwentySeventeen theme [\#1353](https://github.com/WordImpress/Give/issues/1353)
- i18n review after v1.7 release [\#1349](https://github.com/WordImpress/Give/issues/1349)
- In-form login doesn't refresh properly [\#1341](https://github.com/WordImpress/Give/issues/1341)

**Merged pull requests:**

- Release/1.7.2 [\#1356](https://github.com/WordImpress/Give/pull/1356) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1349 [\#1355](https://github.com/WordImpress/Give/pull/1355) ([DevinWalker](https://github.com/DevinWalker))
- TwentySeventeen Compatibility Issue/1353 [\#1354](https://github.com/WordImpress/Give/pull/1354) ([DevinWalker](https://github.com/DevinWalker))
- SCSS optimization for darker backgrounds and minor additional adjustments - Issue/1258 [\#1352](https://github.com/WordImpress/Give/pull/1352) ([DevinWalker](https://github.com/DevinWalker))
- Icon font fix [\#1351](https://github.com/WordImpress/Give/pull/1351) ([DevinWalker](https://github.com/DevinWalker))
- Update to use `cmb2\_init` action and `new\_cmb2\_box` function. [\#1350](https://github.com/WordImpress/Give/pull/1350) ([jtsternberg](https://github.com/jtsternberg))
- Do not render form if [\#1345](https://github.com/WordImpress/Give/pull/1345) ([ravinderk](https://github.com/ravinderk))
- Add selected option class to li tag instead of label [\#1342](https://github.com/WordImpress/Give/pull/1342) ([ravinderk](https://github.com/ravinderk))
- Add test [\#1340](https://github.com/WordImpress/Give/pull/1340) ([ravinderk](https://github.com/ravinderk))
- Add give\_create\_payment filter [\#1339](https://github.com/WordImpress/Give/pull/1339) ([ravinderk](https://github.com/ravinderk))
- Issues/520 [\#1338](https://github.com/WordImpress/Give/pull/1338) ([ravinderk](https://github.com/ravinderk))
- Scrutinizer [\#1337](https://github.com/WordImpress/Give/pull/1337) ([ravinderk](https://github.com/ravinderk))

## [1.7.1](https://github.com/WordImpress/Give/tree/1.7.1) (2016-12-10)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.7...1.7.1)

**Fixed bugs:**

- PayPal gateway is not properly passing the donation form name [\#1334](https://github.com/WordImpress/Give/issues/1334)

**Closed issues:**

- Add filter for Magnific closeOnBgClick option [\#1328](https://github.com/WordImpress/Give/issues/1328)
- WordPress 4.7 adds a grey border around our Welcome images [\#1322](https://github.com/WordImpress/Give/issues/1322)

**Merged pull requests:**

- Release/1.7.1 [\#1336](https://github.com/WordImpress/Give/pull/1336) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1334 [\#1335](https://github.com/WordImpress/Give/pull/1335) ([DevinWalker](https://github.com/DevinWalker))
- Tests [\#1333](https://github.com/WordImpress/Give/pull/1333) ([ravinderk](https://github.com/ravinderk))
- Documentation [\#1332](https://github.com/WordImpress/Give/pull/1332) ([ravinderk](https://github.com/ravinderk))
- Minor Fix [\#1331](https://github.com/WordImpress/Give/pull/1331) ([ravinderk](https://github.com/ravinderk))
- Issue/1328 [\#1329](https://github.com/WordImpress/Give/pull/1329) ([DevinWalker](https://github.com/DevinWalker))
- Offline late escaping [\#1327](https://github.com/WordImpress/Give/pull/1327) ([DevinWalker](https://github.com/DevinWalker))
- Fixed border added by WP 1.7 [\#1326](https://github.com/WordImpress/Give/pull/1326) ([DevinWalker](https://github.com/DevinWalker))
- Scrutinizer score [\#1325](https://github.com/WordImpress/Give/pull/1325) ([ravinderk](https://github.com/ravinderk))
- Add fs-extra to package.json requirements [\#1324](https://github.com/WordImpress/Give/pull/1324) ([mathetos](https://github.com/mathetos))
- Add missing  $low param [\#1323](https://github.com/WordImpress/Give/pull/1323) ([ravinderk](https://github.com/ravinderk))

## [1.7](https://github.com/WordImpress/Give/tree/1.7) (2016-12-08)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.6.4...1.7)

**Implemented enhancements:**

- Allow filter form title on basis of form id and form object [\#1290](https://github.com/WordImpress/Give/issues/1290)
- Donations column needs to be sortable, also rename "price" to "amount" [\#1250](https://github.com/WordImpress/Give/issues/1250)
- Better give/tests/README.md instructions for unit testing  [\#1232](https://github.com/WordImpress/Give/issues/1232)
- Check that activated license URL matches site URL to prevent issues [\#1051](https://github.com/WordImpress/Give/issues/1051)
- Use get\_admin\_page\_title\(\) for admin page titles [\#1028](https://github.com/WordImpress/Give/issues/1028)
- Make transaction columns "Donation Form" and "Status" sortable [\#866](https://github.com/WordImpress/Give/issues/866)
- Develop Give CLI Class [\#841](https://github.com/WordImpress/Give/issues/841)
- Payment date details [\#687](https://github.com/WordImpress/Give/issues/687)
- Need a {receipt\_link\_url} email tag for easier styling [\#581](https://github.com/WordImpress/Give/issues/581)
- Fill form fields with previously entered validated values on give\_send\_back\_to\_checkout\(\) [\#576](https://github.com/WordImpress/Give/issues/576)
- Determine an appropriate \[donation\_history\] "thank you" text by the transaction status. [\#509](https://github.com/WordImpress/Give/issues/509)
- Improve extension license key activation and add weekly status checks [\#357](https://github.com/WordImpress/Give/issues/357)
- Customize HTML5 Alert Messages [\#351](https://github.com/WordImpress/Give/issues/351)

**Fixed bugs:**

- Do not render unpublish or transhed form by shortcode [\#1289](https://github.com/WordImpress/Give/issues/1289)
- Offline gateway issue with pending notification [\#1261](https://github.com/WordImpress/Give/issues/1261)
- Offline Donation donation amount tag not displaying properly [\#1247](https://github.com/WordImpress/Give/issues/1247)
- Multiple donation forms on a page with the Terms checkbox causes jumping [\#1244](https://github.com/WordImpress/Give/issues/1244)
- Give CLI is not working with PHP version 5.3.29 [\#1242](https://github.com/WordImpress/Give/issues/1242)
- Send Test Email button produces PHP Notice [\#1231](https://github.com/WordImpress/Give/issues/1231)
- Changing a payment's donation form breaks in 1.7+ [\#1220](https://github.com/WordImpress/Give/issues/1220)
- Test mode warning not shown by default. [\#1196](https://github.com/WordImpress/Give/issues/1196)
- give\_get\_payment\_status\(\) sometimes returns false for non-English locales [\#1141](https://github.com/WordImpress/Give/issues/1141)
- Updating donor profile results in email error [\#1131](https://github.com/WordImpress/Give/issues/1131)
- Build correct redirect url in give\_send\_back\_to\_checkout if request url already has query param [\#1130](https://github.com/WordImpress/Give/issues/1130)
- Remove credit card related class when credit card type update [\#1121](https://github.com/WordImpress/Give/issues/1121)
- Auto check correct payment gateway [\#1119](https://github.com/WordImpress/Give/issues/1119)
- Show message on form edit screen if form single view is disabled [\#1049](https://github.com/WordImpress/Give/issues/1049)
- Donors unable to see certain transactions in history, depending on logged-in status. [\#983](https://github.com/WordImpress/Give/issues/983)
- Incorrect donor being assigned for logged-in donors when they giving under different name while logged in [\#970](https://github.com/WordImpress/Give/issues/970)
- Encoded HTML and long form titles are going outside of select field in shortcode generator panel [\#804](https://github.com/WordImpress/Give/issues/804)
- Problem with the "Estimated monthly income for this period" amount incorrect [\#773](https://github.com/WordImpress/Give/issues/773)
- Admin transaction details screen's "Donation Details" needs better responsive styling [\#767](https://github.com/WordImpress/Give/issues/767)
- User able to donate minimum amount then custom minimum amount with multi level donation form [\#712](https://github.com/WordImpress/Give/issues/712)
- User Role Cleanup [\#662](https://github.com/WordImpress/Give/issues/662)

**Closed issues:**

- Fix notices appearing on form list table [\#1319](https://github.com/WordImpress/Give/issues/1319)
- --- [\#1302](https://github.com/WordImpress/Give/issues/1302)
- Add Google analytics ecommerce tracking code to donation confirmation page [\#1238](https://github.com/WordImpress/Give/issues/1238)
-  Parse error: syntax error, unexpected '\[' in /plugins/give/includes/admin/forms/class-metabox-form-data.php on line 767 [\#1229](https://github.com/WordImpress/Give/issues/1229)
- Inconsistent naming of class-setting-system-info.php [\#1173](https://github.com/WordImpress/Give/issues/1173)
- Remove bulk edit "Price" field [\#1252](https://github.com/WordImpress/Give/issues/1252)
- Default term & condition label is not showing in form in release/1.7 [\#1240](https://github.com/WordImpress/Give/issues/1240)
- a11y + UX : The terms agreement checkbox should be a required field [\#1200](https://github.com/WordImpress/Give/issues/1200)
- a11y: Shortcodes required fields [\#1193](https://github.com/WordImpress/Give/issues/1193)
- CLI Improvement: id parameter on report command unclear [\#1192](https://github.com/WordImpress/Give/issues/1192)
- CLI Improvement: Export Donor List in Machine Readable Format [\#1191](https://github.com/WordImpress/Give/issues/1191)
- CLI Improvement: Ability to filter donors by form [\#1190](https://github.com/WordImpress/Give/issues/1190)
- Give 1.7: --name parameter does not work on the wp give donors CLI command [\#1189](https://github.com/WordImpress/Give/issues/1189)
- Update CMB2 [\#1188](https://github.com/WordImpress/Give/issues/1188)
- a11y: Payments method list [\#1186](https://github.com/WordImpress/Give/issues/1186)
- a11y: fieldset hierarchy [\#1181](https://github.com/WordImpress/Give/issues/1181)
- a11y: Donation form required fields [\#1178](https://github.com/WordImpress/Give/issues/1178)
- a11y: terms show/hide should be a button not a link [\#1177](https://github.com/WordImpress/Give/issues/1177)
- Slowness on Transactions and Logs screens [\#1172](https://github.com/WordImpress/Give/issues/1172)
- Reverting hooks that use "payment" in 1.7 [\#1160](https://github.com/WordImpress/Give/issues/1160)
- Give form action URL needs escaped differently [\#1146](https://github.com/WordImpress/Give/issues/1146)
- Clicking outside modal should not close the modal [\#1116](https://github.com/WordImpress/Give/issues/1116)
- there is an error in one of the spanish states in the give\_get\_spain\_states\_list function \(line 922\) [\#1113](https://github.com/WordImpress/Give/issues/1113)
- Profile Editor Template [\#1111](https://github.com/WordImpress/Give/issues/1111)
- STOP USING `global $wp\_post\_statuses` [\#1101](https://github.com/WordImpress/Give/issues/1101)
- STOP USING `global $wp\_taxonomies` [\#1099](https://github.com/WordImpress/Give/issues/1099)
- STOP USING `global $wp\_post\_types` [\#1097](https://github.com/WordImpress/Give/issues/1097)
- STOP USING `global $give\_options` [\#1024](https://github.com/WordImpress/Give/issues/1024)
- Error messages in trigger\_error\(\) [\#1019](https://github.com/WordImpress/Give/issues/1019)
- STOP USING `global $pagenow` [\#1017](https://github.com/WordImpress/Give/issues/1017)
- STOP USING `global $wp\_admin\_bar` [\#1015](https://github.com/WordImpress/Give/issues/1015)
- STOP USING `global $current\_user` [\#1013](https://github.com/WordImpress/Give/issues/1013)
- STOP USING `global $wp\_version` [\#1011](https://github.com/WordImpress/Give/issues/1011)
- i18n improvements [\#1005](https://github.com/WordImpress/Give/issues/1005)
- Remove "Form Labels" functions from translation strings [\#1003](https://github.com/WordImpress/Give/issues/1003)
- Automate RTL using gulp [\#995](https://github.com/WordImpress/Give/issues/995)
- Move CSS from `welcome.php` to `welcome.scss` [\#993](https://github.com/WordImpress/Give/issues/993)
- PO/MO language files and i18n improvements in 4.6 [\#967](https://github.com/WordImpress/Give/issues/967)
- Use only one text-domain [\#964](https://github.com/WordImpress/Give/issues/964)
- Fix pre-4.6 backwards compatibility which broke unit tests  [\#957](https://github.com/WordImpress/Give/issues/957)
- Give tabs need consistent nav-tab-wrapper class [\#936](https://github.com/WordImpress/Give/issues/936)
- a11y: donor profile page missing h1 heading [\#934](https://github.com/WordImpress/Give/issues/934)
- a11y: donor stats in donor profile page [\#931](https://github.com/WordImpress/Give/issues/931)
- a11y: progress bar accessibility [\#925](https://github.com/WordImpress/Give/issues/925)
- a11y: newsletter form [\#924](https://github.com/WordImpress/Give/issues/924)
- a11y: table accessibility [\#922](https://github.com/WordImpress/Give/issues/922)
- a11y: check label focus [\#920](https://github.com/WordImpress/Give/issues/920)
- a11y: replace `title` attributes with `aria-label` where needed [\#918](https://github.com/WordImpress/Give/issues/918)
- Transaction page improvements [\#887](https://github.com/WordImpress/Give/issues/887)
- Merge similar translation strings [\#879](https://github.com/WordImpress/Give/issues/879)

**Merged pull requests:**

- Fix \#1319 [\#1321](https://github.com/WordImpress/Give/pull/1321) ([ravinderk](https://github.com/ravinderk))
- Updated screenshots, text and minor CSS edits for welcome screen \#1264 [\#1318](https://github.com/WordImpress/Give/pull/1318) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.7 merging to master for release [\#1317](https://github.com/WordImpress/Give/pull/1317) ([DevinWalker](https://github.com/DevinWalker))
- Update cmb2 backword competibility to WP version 4.7 [\#1316](https://github.com/WordImpress/Give/pull/1316) ([ravinderk](https://github.com/ravinderk))
- Tests [\#1315](https://github.com/WordImpress/Give/pull/1315) ([ravinderk](https://github.com/ravinderk))
- Hotfix/give sidebar [\#1314](https://github.com/WordImpress/Give/pull/1314) ([ravinderk](https://github.com/ravinderk))
- Filter wysiwyg setting field value [\#1313](https://github.com/WordImpress/Give/pull/1313) ([ravinderk](https://github.com/ravinderk))
- issue/1309 [\#1312](https://github.com/WordImpress/Give/pull/1312) ([ravinderk](https://github.com/ravinderk))
- Improving doc blocks / Scrutinizer \#675 [\#1308](https://github.com/WordImpress/Give/pull/1308) ([DevinWalker](https://github.com/DevinWalker))
- Tests [\#1306](https://github.com/WordImpress/Give/pull/1306) ([ravinderk](https://github.com/ravinderk))
- Issues/884 [\#1304](https://github.com/WordImpress/Give/pull/1304) ([ravinderk](https://github.com/ravinderk))
- Issues/679 [\#1303](https://github.com/WordImpress/Give/pull/1303) ([ravinderk](https://github.com/ravinderk))
- Fix setting api [\#1301](https://github.com/WordImpress/Give/pull/1301) ([ravinderk](https://github.com/ravinderk))
- Minor: Fix typo [\#1297](https://github.com/WordImpress/Give/pull/1297) ([ravinderk](https://github.com/ravinderk))
- Issues/867 [\#1294](https://github.com/WordImpress/Give/pull/1294) ([ravinderk](https://github.com/ravinderk))
- Issues/1268 [\#1292](https://github.com/WordImpress/Give/pull/1292) ([ravinderk](https://github.com/ravinderk))
- Hotfix/give form title [\#1291](https://github.com/WordImpress/Give/pull/1291) ([ravinderk](https://github.com/ravinderk))
- Do not render unpublish or transhed form [\#1288](https://github.com/WordImpress/Give/pull/1288) ([ravinderk](https://github.com/ravinderk))
- Issues/850 [\#1282](https://github.com/WordImpress/Give/pull/1282) ([ravinderk](https://github.com/ravinderk))
- Issues/1276 [\#1281](https://github.com/WordImpress/Give/pull/1281) ([ravinderk](https://github.com/ravinderk))
- Issues/520 [\#1280](https://github.com/WordImpress/Give/pull/1280) ([ravinderk](https://github.com/ravinderk))
- Issue/520 revisions [\#1279](https://github.com/WordImpress/Give/pull/1279) ([DevinWalker](https://github.com/DevinWalker))
- Show default title in donation list [\#1277](https://github.com/WordImpress/Give/pull/1277) ([ravinderk](https://github.com/ravinderk))
- Issues/866 [\#1275](https://github.com/WordImpress/Give/pull/1275) ([ravinderk](https://github.com/ravinderk))
- Issues/1269 [\#1274](https://github.com/WordImpress/Give/pull/1274) ([ravinderk](https://github.com/ravinderk))
- Issue/1247 [\#1272](https://github.com/WordImpress/Give/pull/1272) ([DevinWalker](https://github.com/DevinWalker))
- Issues/520 [\#1270](https://github.com/WordImpress/Give/pull/1270) ([ravinderk](https://github.com/ravinderk))
- New licensing UI updates and code review [\#1267](https://github.com/WordImpress/Give/pull/1267) ([DevinWalker](https://github.com/DevinWalker))
- Consolidate offline donation gateway functions [\#1266](https://github.com/WordImpress/Give/pull/1266) ([DevinWalker](https://github.com/DevinWalker))
- Revert "Add line and file name to error notice" [\#1265](https://github.com/WordImpress/Give/pull/1265) ([DevinWalker](https://github.com/DevinWalker))
- Add line and file name to error notice [\#1263](https://github.com/WordImpress/Give/pull/1263) ([ravinderk](https://github.com/ravinderk))
- Issues/1261 [\#1262](https://github.com/WordImpress/Give/pull/1262) ([ravinderk](https://github.com/ravinderk))
- Re issues/997 [\#1257](https://github.com/WordImpress/Give/pull/1257) ([ravinderk](https://github.com/ravinderk))
- refactor give\_get\_payment\_status [\#1256](https://github.com/WordImpress/Give/pull/1256) ([ravinderk](https://github.com/ravinderk))
- Issue/1247 [\#1255](https://github.com/WordImpress/Give/pull/1255) ([DevinWalker](https://github.com/DevinWalker))
- Remove amount field from quick and bulk edit [\#1254](https://github.com/WordImpress/Give/pull/1254) ([ravinderk](https://github.com/ravinderk))
- Issues/1250 [\#1251](https://github.com/WordImpress/Give/pull/1251) ([ravinderk](https://github.com/ravinderk))
- Issue/1119 [\#1248](https://github.com/WordImpress/Give/pull/1248) ([DevinWalker](https://github.com/DevinWalker))
- issue/896 [\#1246](https://github.com/WordImpress/Give/pull/1246) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1244 [\#1245](https://github.com/WordImpress/Give/pull/1245) ([DevinWalker](https://github.com/DevinWalker))
- Do not short array tag because we support PHP 5.3 \>= [\#1243](https://github.com/WordImpress/Give/pull/1243) ([ravinderk](https://github.com/ravinderk))
- show global and default tern and agreement text conditionally [\#1241](https://github.com/WordImpress/Give/pull/1241) ([ravinderk](https://github.com/ravinderk))
- Issue/896 [\#1239](https://github.com/WordImpress/Give/pull/1239) ([DevinWalker](https://github.com/DevinWalker))
-  Minor fix: param is not need in delete\_cache function [\#1237](https://github.com/WordImpress/Give/pull/1237) ([ravinderk](https://github.com/ravinderk))
- Show multi level price if level title is not defined [\#1236](https://github.com/WordImpress/Give/pull/1236) ([ravinderk](https://github.com/ravinderk))
- Issue/1119 [\#1235](https://github.com/WordImpress/Give/pull/1235) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1200 - terms agreement checkbox a11y [\#1234](https://github.com/WordImpress/Give/pull/1234) ([ramiy](https://github.com/ramiy))
- Fixed unit tests and  [\#1233](https://github.com/WordImpress/Give/pull/1233) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1172 [\#1226](https://github.com/WordImpress/Give/pull/1226) ([ravinderk](https://github.com/ravinderk))
- Update update\_donation\_details --\> update\_payment\_details [\#1225](https://github.com/WordImpress/Give/pull/1225) ([ravinderk](https://github.com/ravinderk))
- Issue/662 [\#1224](https://github.com/WordImpress/Give/pull/1224) ([DevinWalker](https://github.com/DevinWalker))
- Updated CMB2 to 2.2.3.1 [\#1222](https://github.com/WordImpress/Give/pull/1222) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1196 [\#1221](https://github.com/WordImpress/Give/pull/1221) ([DevinWalker](https://github.com/DevinWalker))
- Change class-setting-system-info.php file name to follow file naming … [\#1219](https://github.com/WordImpress/Give/pull/1219) ([ravinderk](https://github.com/ravinderk))
- Make type of changes suggestion comment in pr github template [\#1218](https://github.com/WordImpress/Give/pull/1218) ([ravinderk](https://github.com/ravinderk))
- Issues/1191 [\#1217](https://github.com/WordImpress/Give/pull/1217) ([ravinderk](https://github.com/ravinderk))
- Issues/1171 [\#1216](https://github.com/WordImpress/Give/pull/1216) ([ravinderk](https://github.com/ravinderk))
- Issue/826 [\#1214](https://github.com/WordImpress/Give/pull/1214) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Add logo function command to give cli [\#1213](https://github.com/WordImpress/Give/pull/1213) ([ravinderk](https://github.com/ravinderk))
- Issues/1190 [\#1202](https://github.com/WordImpress/Give/pull/1202) ([ravinderk](https://github.com/ravinderk))
- Issues/970 [\#1009](https://github.com/WordImpress/Give/pull/1009) ([ravinderk](https://github.com/ravinderk))
- Issues/957 [\#960](https://github.com/WordImpress/Give/pull/960) ([ravinderk](https://github.com/ravinderk))

## [1.6.4](https://github.com/WordImpress/Give/tree/1.6.4) (2016-11-04)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.6.3...1.6.4)

**Implemented enhancements:**

- New Email tag for just the donation title without level [\#943](https://github.com/WordImpress/Give/issues/943)

**Fixed bugs:**

- "This plugin does not have a valid header" message [\#1208](https://github.com/WordImpress/Give/issues/1208)
- If theme doesn't register image sizes our we get a PHP Warning on the Give Settings page [\#1163](https://github.com/WordImpress/Give/issues/1163)

**Closed issues:**

- a11y: Terms fieldset legend [\#1179](https://github.com/WordImpress/Give/issues/1179)
- User Integration with Events Calendar [\#1133](https://github.com/WordImpress/Give/issues/1133)
- mod\_security flagging our icomoon font [\#794](https://github.com/WordImpress/Give/issues/794)

**Merged pull requests:**

- Release/1.6.4 [\#1210](https://github.com/WordImpress/Give/pull/1210) ([DevinWalker](https://github.com/DevinWalker))
- Fixed {donation} and new {amount} email tag [\#1209](https://github.com/WordImpress/Give/pull/1209) ([DevinWalker](https://github.com/DevinWalker))
- Issue/794 [\#1207](https://github.com/WordImpress/Give/pull/1207) ([DevinWalker](https://github.com/DevinWalker))
- Add license.txt [\#1205](https://github.com/WordImpress/Give/pull/1205) ([mathetos](https://github.com/mathetos))
- Ravinderk issues/724 [\#1203](https://github.com/WordImpress/Give/pull/1203) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1189 [\#1198](https://github.com/WordImpress/Give/pull/1198) ([ravinderk](https://github.com/ravinderk))
- Issue/1193 - Shortcodes required fields [\#1194](https://github.com/WordImpress/Give/pull/1194) ([ramiy](https://github.com/ramiy))
- Issue/1186 - Payments method radio buttons accessibility [\#1187](https://github.com/WordImpress/Give/pull/1187) ([ramiy](https://github.com/ramiy))
- Issue/1177 - Accessible terms display [\#1185](https://github.com/WordImpress/Give/pull/1185) ([ramiy](https://github.com/ramiy))
- Issue/1178 - Use `aria-required=true` in required fields [\#1184](https://github.com/WordImpress/Give/pull/1184) ([ramiy](https://github.com/ramiy))
- Issue/1181 - displays terms fieldset before checkout fieldset, not inside [\#1183](https://github.com/WordImpress/Give/pull/1183) ([ramiy](https://github.com/ramiy))
- Issue/1179 - add \<legend\> to Terms \<fieldset\> [\#1182](https://github.com/WordImpress/Give/pull/1182) ([ramiy](https://github.com/ramiy))
- Escape translation strings [\#1176](https://github.com/WordImpress/Give/pull/1176) ([ramiy](https://github.com/ramiy))
- Minor Fixed [\#1170](https://github.com/WordImpress/Give/pull/1170) ([ravinderk](https://github.com/ravinderk))
- Hide unnessary attachment detail fields [\#1169](https://github.com/WordImpress/Give/pull/1169) ([ravinderk](https://github.com/ravinderk))
- Add image sizes to modal [\#1168](https://github.com/WordImpress/Give/pull/1168) ([ravinderk](https://github.com/ravinderk))
- Provided a default icon "chevron right" \#1078 [\#1167](https://github.com/WordImpress/Give/pull/1167) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1163 [\#1165](https://github.com/WordImpress/Give/pull/1165) ([mathetos](https://github.com/mathetos))
- Issue/1114 [\#1162](https://github.com/WordImpress/Give/pull/1162) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1160 [\#1161](https://github.com/WordImpress/Give/pull/1161) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1036 [\#1158](https://github.com/WordImpress/Give/pull/1158) ([ravinderk](https://github.com/ravinderk))
- Issue/1141 [\#1151](https://github.com/WordImpress/Give/pull/1151) ([kevinwhoffman](https://github.com/kevinwhoffman))

## [1.6.3](https://github.com/WordImpress/Give/tree/1.6.3) (2016-10-26)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.6.2...1.6.3)

**Fixed bugs:**

- PayPal Standard Payment Being Incorrectly Set to Failed [\#1152](https://github.com/WordImpress/Give/issues/1152)
- Custom number of decimals formatting is not working  in give\_sanitize\_amount [\#1144](https://github.com/WordImpress/Give/issues/1144)

**Closed issues:**

- oEmbed of Give Blog Posts on GiveWP.com [\#1138](https://github.com/WordImpress/Give/issues/1138)
- Change the "Completed Donations" language on the Donor Profile tab of the Donor admin page [\#1134](https://github.com/WordImpress/Give/issues/1134)
- Widget UI [\#1086](https://github.com/WordImpress/Give/issues/1086)
- Add a dedication functionality [\#176](https://github.com/WordImpress/Give/issues/176)
- Make the email access message filterable. [\#1147](https://github.com/WordImpress/Give/issues/1147)

**Merged pull requests:**

- Issue/1152 [\#1155](https://github.com/WordImpress/Give/pull/1155) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.6.3 [\#1154](https://github.com/WordImpress/Give/pull/1154) ([DevinWalker](https://github.com/DevinWalker))
- Fix PayPal Standard Failing for Donation Transactions - Issue/1152 [\#1153](https://github.com/WordImpress/Give/pull/1153) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1147 [\#1150](https://github.com/WordImpress/Give/pull/1150) ([ramiy](https://github.com/ramiy))
- Issue/1130 [\#1149](https://github.com/WordImpress/Give/pull/1149) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issues/1144 [\#1145](https://github.com/WordImpress/Give/pull/1145) ([ravinderk](https://github.com/ravinderk))
- Add give\_is\_close\_donation\_form filter hook [\#1142](https://github.com/WordImpress/Give/pull/1142) ([ravinderk](https://github.com/ravinderk))
- Issue/509 [\#1140](https://github.com/WordImpress/Give/pull/1140) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1134 - update the "Completed Forms" title in "Donor Profile" screen [\#1139](https://github.com/WordImpress/Give/pull/1139) ([ramiy](https://github.com/ramiy))
- Issue/1122 [\#1137](https://github.com/WordImpress/Give/pull/1137) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1130 [\#1136](https://github.com/WordImpress/Give/pull/1136) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1131 [\#1135](https://github.com/WordImpress/Give/pull/1135) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Release/1.8 [\#1129](https://github.com/WordImpress/Give/pull/1129) ([ravinderk](https://github.com/ravinderk))
- Issue/1116 [\#1128](https://github.com/WordImpress/Give/pull/1128) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1121 [\#1127](https://github.com/WordImpress/Give/pull/1127) ([kevinwhoffman](https://github.com/kevinwhoffman))
- Issue/1119 [\#1125](https://github.com/WordImpress/Give/pull/1125) ([ramiy](https://github.com/ramiy))
- Issue/1122 [\#1123](https://github.com/WordImpress/Give/pull/1123) ([ramiy](https://github.com/ramiy))
- Issue/1113 - Update spanish "Álava" state in give\_get\_spain\_states\_list\(\) [\#1120](https://github.com/WordImpress/Give/pull/1120) ([ramiy](https://github.com/ramiy))
- Issues/896 [\#1118](https://github.com/WordImpress/Give/pull/1118) ([ravinderk](https://github.com/ravinderk))
- Issue/1111 - Fix profile editor field alignment [\#1115](https://github.com/WordImpress/Give/pull/1115) ([ramiy](https://github.com/ramiy))
- Issue/896 [\#1110](https://github.com/WordImpress/Give/pull/1110) ([ravinderk](https://github.com/ravinderk))
- README.MD: add "built with gulp" badge :-\) [\#1109](https://github.com/WordImpress/Give/pull/1109) ([ramiy](https://github.com/ramiy))
- i18n: Fix wrong use to translation string function [\#1107](https://github.com/WordImpress/Give/pull/1107) ([ramiy](https://github.com/ramiy))
- Fix admin\_url\(\) function usage [\#1106](https://github.com/WordImpress/Give/pull/1106) ([ramiy](https://github.com/ramiy))
- issues/896 [\#1104](https://github.com/WordImpress/Give/pull/1104) ([ravinderk](https://github.com/ravinderk))
- Comment pr \#980 code [\#1103](https://github.com/WordImpress/Give/pull/1103) ([ravinderk](https://github.com/ravinderk))
- Issue/1101 - STOP USING `global $wp\_post\_statuses` [\#1102](https://github.com/WordImpress/Give/pull/1102) ([ramiy](https://github.com/ramiy))
- Issue/1099 - STOP USING `global $wp\_taxonomies` [\#1100](https://github.com/WordImpress/Give/pull/1100) ([ramiy](https://github.com/ramiy))
- Issue/1097 - STOP USING `global $wp\_post\_types` [\#1098](https://github.com/WordImpress/Give/pull/1098) ([ramiy](https://github.com/ramiy))
- Issue/1017 - STOP USING `global $pagenow` [\#1096](https://github.com/WordImpress/Give/pull/1096) ([ramiy](https://github.com/ramiy))
- update phpDocs [\#1095](https://github.com/WordImpress/Give/pull/1095) ([ramiy](https://github.com/ramiy))
- Issues/1062 [\#1093](https://github.com/WordImpress/Give/pull/1093) ([ravinderk](https://github.com/ravinderk))
- Issue/1049 - conditional edit messages [\#1092](https://github.com/WordImpress/Give/pull/1092) ([ramiy](https://github.com/ramiy))
- Test: remove unneeded test check [\#1090](https://github.com/WordImpress/Give/pull/1090) ([ramiy](https://github.com/ramiy))
- Issues/1065 [\#1089](https://github.com/WordImpress/Give/pull/1089) ([ravinderk](https://github.com/ravinderk))
- Fix typo in change log [\#1088](https://github.com/WordImpress/Give/pull/1088) ([GaryJones](https://github.com/GaryJones))
- Issues/576 [\#1068](https://github.com/WordImpress/Give/pull/1068) ([ravinderk](https://github.com/ravinderk))

## [1.6.2](https://github.com/WordImpress/Give/tree/1.6.2) (2016-10-04)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.6.1...1.6.2)

**Implemented enhancements:**

- Automation: minify plugin images [\#1035](https://github.com/WordImpress/Give/issues/1035)
- Add option to widget to allow opening in modal/reveal [\#1034](https://github.com/WordImpress/Give/issues/1034)

**Fixed bugs:**

- PayPal Standard w/ donation form that has an apostrophe issue and processing message [\#1079](https://github.com/WordImpress/Give/issues/1079)
- Stop donor from saving empty email address [\#999](https://github.com/WordImpress/Give/issues/999)

**Closed issues:**

- Dynamically update giving form Amount [\#1057](https://github.com/WordImpress/Give/issues/1057)
- New Issue & PR Template and .github directory [\#1052](https://github.com/WordImpress/Give/issues/1052)
- Unifying the addons action-links in "plugin" screen [\#1033](https://github.com/WordImpress/Give/issues/1033)
- Addon alerts have RTL issue [\#1031](https://github.com/WordImpress/Give/issues/1031)
- Update readme.txt changelog so Github links have nicer hyperlink [\#1083](https://github.com/WordImpress/Give/issues/1083)
- Improve add-on activation banner style [\#1081](https://github.com/WordImpress/Give/issues/1081)
- Use radio buttons for floating label setting option in widget [\#1042](https://github.com/WordImpress/Give/issues/1042)
- Use i18n module [\#1021](https://github.com/WordImpress/Give/issues/1021)

**Merged pull requests:**

- Release/1.6.2 [\#1085](https://github.com/WordImpress/Give/pull/1085) ([DevinWalker](https://github.com/DevinWalker))
- Readme.txt improvements [\#1084](https://github.com/WordImpress/Give/pull/1084) ([DevinWalker](https://github.com/DevinWalker))
- New i18n banner and Add-on activation banner improvements [\#1082](https://github.com/WordImpress/Give/pull/1082) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1079 [\#1080](https://github.com/WordImpress/Give/pull/1080) ([DevinWalker](https://github.com/DevinWalker))
- Issue/976 [\#1077](https://github.com/WordImpress/Give/pull/1077) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1073 [\#1076](https://github.com/WordImpress/Give/pull/1076) ([ravinderk](https://github.com/ravinderk))
- Fix bulk action issue for api setting tab [\#1075](https://github.com/WordImpress/Give/pull/1075) ([ravinderk](https://github.com/ravinderk))
- Issue/281 text icons [\#1072](https://github.com/WordImpress/Give/pull/1072) ([DevinWalker](https://github.com/DevinWalker))
- Issues/1061 [\#1071](https://github.com/WordImpress/Give/pull/1071) ([ravinderk](https://github.com/ravinderk))
- Release/1.8 [\#1070](https://github.com/WordImpress/Give/pull/1070) ([ravinderk](https://github.com/ravinderk))
- Translate deprecated hook notice [\#1067](https://github.com/WordImpress/Give/pull/1067) ([ramiy](https://github.com/ramiy))
- Issue/1017 - STOP USING `global $pagenow` [\#1066](https://github.com/WordImpress/Give/pull/1066) ([ramiy](https://github.com/ramiy))
- Issues/1051 [\#1060](https://github.com/WordImpress/Give/pull/1060) ([ravinderk](https://github.com/ravinderk))
- Issues/668 [\#1059](https://github.com/WordImpress/Give/pull/1059) ([ravinderk](https://github.com/ravinderk))
- Give License [\#1058](https://github.com/WordImpress/Give/pull/1058) ([ramiy](https://github.com/ramiy))
- Issue/1035 - Automation: minify plugin images [\#1056](https://github.com/WordImpress/Give/pull/1056) ([ramiy](https://github.com/ramiy))
- Convert float label widget setting to radio button [\#1048](https://github.com/WordImpress/Give/pull/1048) ([ravinderk](https://github.com/ravinderk))
- Plugin action links [\#1045](https://github.com/WordImpress/Give/pull/1045) ([ramiy](https://github.com/ramiy))
- Scrutinizer Docs [\#1044](https://github.com/WordImpress/Give/pull/1044) ([ramiy](https://github.com/ramiy))
- minor updates [\#1043](https://github.com/WordImpress/Give/pull/1043) ([ramiy](https://github.com/ramiy))
- Add option to widget to allow opening in modal/reveal [\#1041](https://github.com/WordImpress/Give/pull/1041) ([ravinderk](https://github.com/ravinderk))
- Issues/999 [\#1040](https://github.com/WordImpress/Give/pull/1040) ([ravinderk](https://github.com/ravinderk))
- Issue/1031 - Addon alerts RTL issue [\#1032](https://github.com/WordImpress/Give/pull/1032) ([ramiy](https://github.com/ramiy))
- Issue/1028 - Start using `get\_admin\_page\_title\(\)` for sreen titles [\#1029](https://github.com/WordImpress/Give/pull/1029) ([ramiy](https://github.com/ramiy))
- Scrutinizer Comprehensibility [\#1026](https://github.com/WordImpress/Give/pull/1026) ([ramiy](https://github.com/ramiy))
- Scrutinizer braces [\#1025](https://github.com/WordImpress/Give/pull/1025) ([ramiy](https://github.com/ramiy))
- Issue/1013 - Use `$current\_user = wp\_get\_current\_user\(\)` instead of `global $current\_user` [\#1023](https://github.com/WordImpress/Give/pull/1023) ([ramiy](https://github.com/ramiy))
- Docs: globals [\#1022](https://github.com/WordImpress/Give/pull/1022) ([ramiy](https://github.com/ramiy))
- Issue/1019 - Error messages in trigger\_error\(\) [\#1020](https://github.com/WordImpress/Give/pull/1020) ([ramiy](https://github.com/ramiy))
- Issue/960 - globals in deprecated filters/actions [\#1018](https://github.com/WordImpress/Give/pull/1018) ([ramiy](https://github.com/ramiy))
- Issue/1015 - Use the action parameter instead of `global $wp\_admin\_bar` [\#1016](https://github.com/WordImpress/Give/pull/1016) ([ramiy](https://github.com/ramiy))
- Issue/1013 - Use `$current\_user = wp\_get\_current\_user\(\)` instead of `global $current\_user` [\#1014](https://github.com/WordImpress/Give/pull/1014) ([ramiy](https://github.com/ramiy))
- Issue/1011 - Use `get\_bloginfo\( 'version' \)` instead of `global $wp\_version` [\#1012](https://github.com/WordImpress/Give/pull/1012) ([ramiy](https://github.com/ramiy))
- Issue/1005 - i18n improvements [\#1006](https://github.com/WordImpress/Give/pull/1006) ([ramiy](https://github.com/ramiy))

## [1.6.1](https://github.com/WordImpress/Give/tree/1.6.1) (2016-09-06)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.6...1.6.1)

**Implemented enhancements:**

- New decimal option should be a number field with a max of 3 [\#862](https://github.com/WordImpress/Give/issues/862)
- Dynamic Hooks [\#897](https://github.com/WordImpress/Give/issues/897)

**Fixed bugs:**

- Notices generated when user submit donation form [\#954](https://github.com/WordImpress/Give/issues/954)
- Pre-populate First and Last name in PayPal Standard [\#945](https://github.com/WordImpress/Give/issues/945)
- Apostrophe's break how Goals amounts are output [\#914](https://github.com/WordImpress/Give/issues/914)
- Thousands separator doesn't respect apostrophe's [\#913](https://github.com/WordImpress/Give/issues/913)
- Currency icon css issue for money field [\#902](https://github.com/WordImpress/Give/issues/902)
- Date incorrect in transaction details view [\#898](https://github.com/WordImpress/Give/issues/898)
- Switching donation form in transaction view "-2" appears when no form set [\#877](https://github.com/WordImpress/Give/issues/877)
- Blank preview email for new installs that haven't saved options [\#863](https://github.com/WordImpress/Give/issues/863)
- Payment errors log missing gateway data and payment ID column issue [\#780](https://github.com/WordImpress/Give/issues/780)

**Closed issues:**

- Styling issue on donor profile editor page [\#997](https://github.com/WordImpress/Give/issues/997)
- a11y: review row action links accessibility [\#948](https://github.com/WordImpress/Give/issues/948)
- a11y: View Details [\#946](https://github.com/WordImpress/Give/issues/946)
- Id columns [\#915](https://github.com/WordImpress/Give/issues/915)
- Css conflict in donation information metabox [\#906](https://github.com/WordImpress/Give/issues/906)
- Add Support for Beaver Builder [\#690](https://github.com/WordImpress/Give/issues/690)
- Readme.txt screenshot issues [\#990](https://github.com/WordImpress/Give/issues/990)
- Encode html link print for empty offline donation message [\#981](https://github.com/WordImpress/Give/issues/981)
- Donation amount is not updating while changing payment gaetway [\#956](https://github.com/WordImpress/Give/issues/956)
- Revert Settings AJAX tabs from \#494 [\#937](https://github.com/WordImpress/Give/issues/937)

**Merged pull requests:**

- Default email receipt value upon install [\#1008](https://github.com/WordImpress/Give/pull/1008) ([DevinWalker](https://github.com/DevinWalker))
- PayPal first and last name support [\#1007](https://github.com/WordImpress/Give/pull/1007) ([DevinWalker](https://github.com/DevinWalker))
- Issue/1003 - Remove "Form Labels" functions from translation strings [\#1004](https://github.com/WordImpress/Give/pull/1004) ([ramiy](https://github.com/ramiy))
- Issue/995 - Automate RTL using gulp [\#1002](https://github.com/WordImpress/Give/pull/1002) ([ramiy](https://github.com/ramiy))
- Issues/997 [\#998](https://github.com/WordImpress/Give/pull/998) ([ravinderk](https://github.com/ravinderk))
- i18n Improvements [\#996](https://github.com/WordImpress/Give/pull/996) ([ramiy](https://github.com/ramiy))
- Issue/993 - Move CSS from `welcome.php` to `welcome.scss` [\#994](https://github.com/WordImpress/Give/pull/994) ([ramiy](https://github.com/ramiy))
- Use missing  parameter [\#989](https://github.com/WordImpress/Give/pull/989) ([ravinderk](https://github.com/ravinderk))
- Issue/943 - Fix the title level text in {donation} email template tag [\#988](https://github.com/WordImpress/Give/pull/988) ([ramiy](https://github.com/ramiy))
- Issue/896 - Terminology: Donators \> Donors [\#987](https://github.com/WordImpress/Give/pull/987) ([ramiy](https://github.com/ramiy))
- Release/1.6.1 [\#984](https://github.com/WordImpress/Give/pull/984) ([DevinWalker](https://github.com/DevinWalker))
- Fix issue \#981 [\#982](https://github.com/WordImpress/Give/pull/982) ([ravinderk](https://github.com/ravinderk))
- Override per form term and condition empty settings with global setting [\#980](https://github.com/WordImpress/Give/pull/980) ([ravinderk](https://github.com/ravinderk))
- minor updates [\#978](https://github.com/WordImpress/Give/pull/978) ([ramiy](https://github.com/ramiy))
- Issue/967 - language files [\#977](https://github.com/WordImpress/Give/pull/977) ([ramiy](https://github.com/ramiy))
- Make exception message translatable [\#974](https://github.com/WordImpress/Give/pull/974) ([ramiy](https://github.com/ramiy))
- Issues/957 [\#973](https://github.com/WordImpress/Give/pull/973) ([ravinderk](https://github.com/ravinderk))
- Fix typo: "login" is a noun or adjective. "log in" is the verb. [\#971](https://github.com/WordImpress/Give/pull/971) ([Benunc](https://github.com/Benunc))
- Cleanup all option \(transients\) when user uninstall plugin [\#969](https://github.com/WordImpress/Give/pull/969) ([ravinderk](https://github.com/ravinderk))
- Issue/964 - gulp text-domain task [\#966](https://github.com/WordImpress/Give/pull/966) ([ramiy](https://github.com/ramiy))
- Update doc block in formatting.php [\#965](https://github.com/WordImpress/Give/pull/965) ([ravinderk](https://github.com/ravinderk))
- Issues/773 [\#963](https://github.com/WordImpress/Give/pull/963) ([ravinderk](https://github.com/ravinderk))
- Issue/876 - Shorten description strings in Give Settings Screen [\#961](https://github.com/WordImpress/Give/pull/961) ([ramiy](https://github.com/ramiy))
- Donor screen - view form table [\#958](https://github.com/WordImpress/Give/pull/958) ([ramiy](https://github.com/ramiy))
- Hotfix/fix notices [\#955](https://github.com/WordImpress/Give/pull/955) ([ravinderk](https://github.com/ravinderk))
- Issue/581 - Add {receipt\_link\_url} email tag [\#953](https://github.com/WordImpress/Give/pull/953) ([ramiy](https://github.com/ramiy))
- Issues/804 [\#952](https://github.com/WordImpress/Give/pull/952) ([ravinderk](https://github.com/ravinderk))
- issues/687 [\#951](https://github.com/WordImpress/Give/pull/951) ([ravinderk](https://github.com/ravinderk))
- Issues/896 [\#950](https://github.com/WordImpress/Give/pull/950) ([ravinderk](https://github.com/ravinderk))
- Issue/948 - row action links accessibility [\#949](https://github.com/WordImpress/Give/pull/949) ([ramiy](https://github.com/ramiy))
- Issue/946 - replace generic "View Details" string with specific strings [\#947](https://github.com/WordImpress/Give/pull/947) ([ramiy](https://github.com/ramiy))
- Issues/357 [\#944](https://github.com/WordImpress/Give/pull/944) ([ravinderk](https://github.com/ravinderk))
- issues/914 [\#942](https://github.com/WordImpress/Give/pull/942) ([ravinderk](https://github.com/ravinderk))
- Issue/780 [\#941](https://github.com/WordImpress/Give/pull/941) ([DevinWalker](https://github.com/DevinWalker))
- Settings tab revert  [\#940](https://github.com/WordImpress/Give/pull/940) ([DevinWalker](https://github.com/DevinWalker))
- Issue/924 - newsletter form accessibility [\#939](https://github.com/WordImpress/Give/pull/939) ([ramiy](https://github.com/ramiy))
- Issue/936 - Use WordPress `.nav-tab-wrapper` class in Donor screen tabs [\#938](https://github.com/WordImpress/Give/pull/938) ([ramiy](https://github.com/ramiy))
- Issue/934 - add H1 heading to admin "Donor" screen [\#935](https://github.com/WordImpress/Give/pull/935) ([ramiy](https://github.com/ramiy))
- Dynamic filter hooks [\#932](https://github.com/WordImpress/Give/pull/932) ([ramiy](https://github.com/ramiy))
- Fix issues \#712 [\#930](https://github.com/WordImpress/Give/pull/930) ([ravinderk](https://github.com/ravinderk))
- issues/902 [\#929](https://github.com/WordImpress/Give/pull/929) ([ravinderk](https://github.com/ravinderk))
- Issues/913 [\#928](https://github.com/WordImpress/Give/pull/928) ([ravinderk](https://github.com/ravinderk))
- Issue/925 - progress bar accessibility [\#927](https://github.com/WordImpress/Give/pull/927) ([ramiy](https://github.com/ramiy))
- Issue/922 - table accessibility [\#923](https://github.com/WordImpress/Give/pull/923) ([ramiy](https://github.com/ramiy))
- Issue/920 - check label focus [\#921](https://github.com/WordImpress/Give/pull/921) ([ramiy](https://github.com/ramiy))
- Issue/918 - replace `title` attributes with `aria-label` [\#919](https://github.com/WordImpress/Give/pull/919) ([ramiy](https://github.com/ramiy))
- Docs: document template action hooks [\#917](https://github.com/WordImpress/Give/pull/917) ([ramiy](https://github.com/ramiy))
- Terminology: Transaction \> Donation [\#912](https://github.com/WordImpress/Give/pull/912) ([ramiy](https://github.com/ramiy))
- Simpler code for "contributor list" [\#911](https://github.com/WordImpress/Give/pull/911) ([ramiy](https://github.com/ramiy))
- Docs: document customer action hooks [\#910](https://github.com/WordImpress/Give/pull/910) ([ramiy](https://github.com/ramiy))
- minor title updates [\#909](https://github.com/WordImpress/Give/pull/909) ([ramiy](https://github.com/ramiy))
- Fix issue \#902 [\#908](https://github.com/WordImpress/Give/pull/908) ([ravinderk](https://github.com/ravinderk))
- Issues/351 [\#907](https://github.com/WordImpress/Give/pull/907) ([ravinderk](https://github.com/ravinderk))
- Transaction Details \> Donor Details Metabox [\#905](https://github.com/WordImpress/Give/pull/905) ([ramiy](https://github.com/ramiy))
- Transaction Details \> Donation Details Metabox \(Responsiveness\) [\#904](https://github.com/WordImpress/Give/pull/904) ([ramiy](https://github.com/ramiy))
- Prevent generate give action related notices [\#901](https://github.com/WordImpress/Give/pull/901) ([ravinderk](https://github.com/ravinderk))
- Dynamic action hooks structure [\#900](https://github.com/WordImpress/Give/pull/900) ([ramiy](https://github.com/ramiy))
- Document action hooks [\#899](https://github.com/WordImpress/Give/pull/899) ([ramiy](https://github.com/ramiy))
- a11y: add role="search" for better accessibility [\#895](https://github.com/WordImpress/Give/pull/895) ([ramiy](https://github.com/ramiy))
- Docs: document action hooks [\#894](https://github.com/WordImpress/Give/pull/894) ([ramiy](https://github.com/ramiy))
- a11y: remove "title" attributes for better accessibility [\#891](https://github.com/WordImpress/Give/pull/891) ([ramiy](https://github.com/ramiy))
- a11y: transaction page [\#888](https://github.com/WordImpress/Give/pull/888) ([ramiy](https://github.com/ramiy))
- Make transaction columns "Donation Form" and "Status" sortable [\#886](https://github.com/WordImpress/Give/pull/886) ([ramiy](https://github.com/ramiy))
- i18n Improvements [\#885](https://github.com/WordImpress/Give/pull/885) ([ramiy](https://github.com/ramiy))
- Merge similar translation strings [\#883](https://github.com/WordImpress/Give/pull/883) ([ramiy](https://github.com/ramiy))
- Give cli [\#882](https://github.com/WordImpress/Give/pull/882) ([ravinderk](https://github.com/ravinderk))
- Fix issues \#877 [\#880](https://github.com/WordImpress/Give/pull/880) ([ravinderk](https://github.com/ravinderk))
- i18n improvements [\#878](https://github.com/WordImpress/Give/pull/878) ([ramiy](https://github.com/ramiy))
- phpDocs - documenting give actions [\#875](https://github.com/WordImpress/Give/pull/875) ([ramiy](https://github.com/ramiy))
- Text Change - replace "Lost Password?" with "Reset Password" [\#874](https://github.com/WordImpress/Give/pull/874) ([ramiy](https://github.com/ramiy))
- i18n: add esc\_html\_\_\(\) function to make the string translatable [\#873](https://github.com/WordImpress/Give/pull/873) ([ramiy](https://github.com/ramiy))
- a11y - add 'for' attributes to labels [\#872](https://github.com/WordImpress/Give/pull/872) ([ramiy](https://github.com/ramiy))

## [1.6](https://github.com/WordImpress/Give/tree/1.6) (2016-08-11)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.5.2...1.6)

**Implemented enhancements:**

- FPDF Library is outdated [\#756](https://github.com/WordImpress/Give/issues/756)
- Add Conditional Functions [\#832](https://github.com/WordImpress/Give/issues/832)
- Unify wp\_die\(\) all over the plugin. Add title and HTML status response codes. [\#828](https://github.com/WordImpress/Give/issues/828)
- Auto select api key when click on input field on apikey list page [\#823](https://github.com/WordImpress/Give/issues/823)
- Adjust donor name and donation ID column in transaction list [\#814](https://github.com/WordImpress/Give/issues/814)
- Allow developers to skip gateways by filter when changing pending payments to abandon payments [\#809](https://github.com/WordImpress/Give/issues/809)
- Allow developers to add a custom logo image to "Export PDF of Donations and Income" [\#802](https://github.com/WordImpress/Give/issues/802)
- Add setting to set number of decimal points shown and saved in prices [\#738](https://github.com/WordImpress/Give/issues/738)
- Allow user to add custom logout url to give\_login shortcode [\#702](https://github.com/WordImpress/Give/issues/702)
- Update the country dropdown to use the new ISO country codes for islands in Dutch West Indies [\#698](https://github.com/WordImpress/Give/issues/698)
- Introduce Customer Meta Table & Class w/ CRUD Methods [\#653](https://github.com/WordImpress/Give/issues/653)
- Add a way to make large goals \(and progress\) more readable. [\#650](https://github.com/WordImpress/Give/issues/650)
- Switch a transaction to a different form [\#429](https://github.com/WordImpress/Give/issues/429)
- Add template for Give Goals [\#411](https://github.com/WordImpress/Give/issues/411)

**Fixed bugs:**

- Amount not formatting after adding a new level to multi-level form [\#864](https://github.com/WordImpress/Give/issues/864)
- Transaction ID link goes to 404 page [\#865](https://github.com/WordImpress/Give/issues/865)
- PHP 5.2  Fatal error: Can't use function return value in write context [\#860](https://github.com/WordImpress/Give/issues/860)
- Add email tag support for offline donation admin notification [\#846](https://github.com/WordImpress/Give/issues/846)
- "Author" User role has a useless shortcode generator on pages and posts [\#845](https://github.com/WordImpress/Give/issues/845)
- Dashboard donation stats widget not counting test payments and incorrect \# formatting [\#837](https://github.com/WordImpress/Give/issues/837)
- Wrong total api key count on api key list page [\#821](https://github.com/WordImpress/Give/issues/821)
- Transaction list and donor donation list design break on tablet and mobile [\#813](https://github.com/WordImpress/Give/issues/813)
- esc\_html doesn't expect a textdomain [\#781](https://github.com/WordImpress/Give/issues/781)
- Amount field not formatting on focus out & lingering tooltip issues  [\#778](https://github.com/WordImpress/Give/issues/778)
- Issue with padlock icon CSS [\#769](https://github.com/WordImpress/Give/issues/769)
- Mixed content warning with placeholder image [\#768](https://github.com/WordImpress/Give/issues/768)
- Goal format not respecting space for thousands separator [\#764](https://github.com/WordImpress/Give/issues/764)
- Bug related to i18n esc\_attr\_\_ usage [\#759](https://github.com/WordImpress/Give/issues/759)
- Export Donation History no longer has Form Title and also has unnecessary columns [\#757](https://github.com/WordImpress/Give/issues/757)
- Unrendered html in multi-level donation confirmation emails. [\#754](https://github.com/WordImpress/Give/issues/754)
- Reports Tooltip & Donation Counts Needs Proper Formatting [\#749](https://github.com/WordImpress/Give/issues/749)
- Admin Amount Field Tooltip Issues Related to \#734 [\#747](https://github.com/WordImpress/Give/issues/747)
- Multiple modal popups with "show terms" on one page lead to JS issues. [\#741](https://github.com/WordImpress/Give/issues/741)
- Auto populate correct donation level when user adds a matching custom amount [\#730](https://github.com/WordImpress/Give/issues/730)
- Donation stat for donor and form is not decrease when payment status change from complete to cancel [\#726](https://github.com/WordImpress/Give/issues/726)
- Categories and Tags are registering incorrectly on a fresh install [\#725](https://github.com/WordImpress/Give/issues/725)
- Form stats increase each time the payment status changes from revoked to complete [\#722](https://github.com/WordImpress/Give/issues/722)
- Unable to see payment history for donors with unusual characters in the email [\#717](https://github.com/WordImpress/Give/issues/717)
- Auto populate state list is not working on transaction detail page [\#715](https://github.com/WordImpress/Give/issues/715)
- Update user lifetime donation stat when donation amount changed in payment in wp backend [\#714](https://github.com/WordImpress/Give/issues/714)
- Prevent admin/give user to save negative donation amount from wp backend [\#708](https://github.com/WordImpress/Give/issues/708)
- Need to add better support for the use of a space as thousands separator value [\#696](https://github.com/WordImpress/Give/issues/696)
- Amount formatting issue [\#693](https://github.com/WordImpress/Give/issues/693)
- Text editor do not have same height for visual mode and text mode. [\#688](https://github.com/WordImpress/Give/issues/688)
- Email Previews & Add-on Specific Email Tags Don't Display [\#274](https://github.com/WordImpress/Give/issues/274)
- Form stats increase each time the payment status changes from revoked to complete [\#785](https://github.com/WordImpress/Give/pull/785) ([ravinderk](https://github.com/ravinderk))

**Closed issues:**

- Update Google Logo on Mashup Metabox [\#739](https://github.com/WordImpress/Give/issues/739)
- Prevent user to save non numeric or negative amount [\#705](https://github.com/WordImpress/Give/issues/705)
- Dedicated Funds for donations [\#701](https://github.com/WordImpress/Give/issues/701)
- Dismissing notices doesn't dismiss them on refresh [\#365](https://github.com/WordImpress/Give/issues/365)
- Update floating labels links to go to our documentation [\#869](https://github.com/WordImpress/Give/issues/869)
- Role typo causing stats not to display properly for new installs [\#861](https://github.com/WordImpress/Give/issues/861)
- Accessibility Headings [\#820](https://github.com/WordImpress/Give/issues/820)
- Css conflict appear when users see receipt in browser & admins preview emails in wp-admin [\#818](https://github.com/WordImpress/Give/issues/818)
- i18n: Add translator comments [\#746](https://github.com/WordImpress/Give/issues/746)
- RTL Audit & Resolution [\#736](https://github.com/WordImpress/Give/issues/736)
- Remove @description due to not being PHP DocBlock compliance [\#733](https://github.com/WordImpress/Give/issues/733)
- Update to latest version of CMB2 & Test Compatiblity [\#670](https://github.com/WordImpress/Give/issues/670)
- Incorporate proper sanitization for i18n strings [\#471](https://github.com/WordImpress/Give/issues/471)
- Fix translatable strings according to GlotPress [\#451](https://github.com/WordImpress/Give/issues/451)

**Merged pull requests:**

- Amount not formatting after adding a new level to multi-level form [\#870](https://github.com/WordImpress/Give/pull/870) ([ravinderk](https://github.com/ravinderk))
- Transaction column fixes and touch ups \#865 [\#868](https://github.com/WordImpress/Give/pull/868) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.6 [\#859](https://github.com/WordImpress/Give/pull/859) ([DevinWalker](https://github.com/DevinWalker))
- a11y in give shortcode [\#858](https://github.com/WordImpress/Give/pull/858) ([ramiy](https://github.com/ramiy))
- phpDocs [\#856](https://github.com/WordImpress/Give/pull/856) ([ramiy](https://github.com/ramiy))
- phpDocs [\#855](https://github.com/WordImpress/Give/pull/855) ([ramiy](https://github.com/ramiy))
- Ravinderk hotfix/issues/814 [\#854](https://github.com/WordImpress/Give/pull/854) ([DevinWalker](https://github.com/DevinWalker))
- removes EDD language [\#851](https://github.com/WordImpress/Give/pull/851) ([Benunc](https://github.com/Benunc))
- Add check for ability to edit give forms [\#849](https://github.com/WordImpress/Give/pull/849) ([Benunc](https://github.com/Benunc))
- phpDocs - various updates [\#848](https://github.com/WordImpress/Give/pull/848) ([ramiy](https://github.com/ramiy))
- Fixes Admin Offline Donation Notification [\#847](https://github.com/WordImpress/Give/pull/847) ([Benunc](https://github.com/Benunc))
- minor context update [\#843](https://github.com/WordImpress/Give/pull/843) ([ramiy](https://github.com/ramiy))
- Fixed incorrect formatting in dashboard widget \#837 [\#840](https://github.com/WordImpress/Give/pull/840) ([DevinWalker](https://github.com/DevinWalker))
- a11y: use WordPress default screen-reader class [\#829](https://github.com/WordImpress/Give/pull/829) ([ramiy](https://github.com/ramiy))
- issues/675 [\#825](https://github.com/WordImpress/Give/pull/825) ([ravinderk](https://github.com/ravinderk))
- Auto select api key when click on input field [\#824](https://github.com/WordImpress/Give/pull/824) ([ravinderk](https://github.com/ravinderk))
- Fix transient key name [\#822](https://github.com/WordImpress/Give/pull/822) ([ravinderk](https://github.com/ravinderk))
- New filter for PDF export logo [\#816](https://github.com/WordImpress/Give/pull/816) ([DevinWalker](https://github.com/DevinWalker))
- i18n sucurity: escaping translation strings - Resolve Merge Conflict [\#811](https://github.com/WordImpress/Give/pull/811) ([DevinWalker](https://github.com/DevinWalker))
- Do not pass more then three params to remove\_filter [\#808](https://github.com/WordImpress/Give/pull/808) ([ravinderk](https://github.com/ravinderk))
- Issues/411 [\#805](https://github.com/WordImpress/Give/pull/805) ([ravinderk](https://github.com/ravinderk))
- Issues/757 [\#803](https://github.com/WordImpress/Give/pull/803) ([ravinderk](https://github.com/ravinderk))
- Corrects capitalization for proper localization [\#799](https://github.com/WordImpress/Give/pull/799) ([Benunc](https://github.com/Benunc))
- update readme links "http" =\> "https" [\#798](https://github.com/WordImpress/Give/pull/798) ([ramiy](https://github.com/ramiy))
- update readme file typo "the the" =\> "the" [\#797](https://github.com/WordImpress/Give/pull/797) ([ramiy](https://github.com/ramiy))
- Issue/725 [\#793](https://github.com/WordImpress/Give/pull/793) ([DevinWalker](https://github.com/DevinWalker))
- Makes country names translatable [\#786](https://github.com/WordImpress/Give/pull/786) ([Benunc](https://github.com/Benunc))
- Missing code [\#783](https://github.com/WordImpress/Give/pull/783) ([ravinderk](https://github.com/ravinderk))
- i18n: don't esc strings with html code [\#779](https://github.com/WordImpress/Give/pull/779) ([ramiy](https://github.com/ramiy))
- Fix amount style in form header for larger donation [\#777](https://github.com/WordImpress/Give/pull/777) ([ravinderk](https://github.com/ravinderk))
- i18n sucurity: escaping translation strings [\#776](https://github.com/WordImpress/Give/pull/776) ([ramiy](https://github.com/ramiy))
- Fixed TinyMCE height issue \#688 [\#765](https://github.com/WordImpress/Give/pull/765) ([DevinWalker](https://github.com/DevinWalker))
- Ravinderk issues/650 [\#763](https://github.com/WordImpress/Give/pull/763) ([DevinWalker](https://github.com/DevinWalker))
- strips the tags from the donation form [\#762](https://github.com/WordImpress/Give/pull/762) ([Benunc](https://github.com/Benunc))
- i18n: fix html esc issue [\#760](https://github.com/WordImpress/Give/pull/760) ([ramiy](https://github.com/ramiy))
- i18n: Add translator comments [\#753](https://github.com/WordImpress/Give/pull/753) ([ramiy](https://github.com/ramiy))
- Reports Tooltip & Dohttps://github.com/WordImpress/Give/pull/751nation Counts Needs Proper Formatting [\#751](https://github.com/WordImpress/Give/pull/751) ([ravinderk](https://github.com/ravinderk))
- Admin Amount Field Tooltip Issues Related to [\#748](https://github.com/WordImpress/Give/pull/748) ([ravinderk](https://github.com/ravinderk))
- i18n Improvments [\#745](https://github.com/WordImpress/Give/pull/745) ([ramiy](https://github.com/ramiy))
- Remove @description tag from code documentation [\#743](https://github.com/WordImpress/Give/pull/743) ([ravinderk](https://github.com/ravinderk))
- Text Changes: periods [\#740](https://github.com/WordImpress/Give/pull/740) ([ramiy](https://github.com/ramiy))
- Permission Error Messages [\#737](https://github.com/WordImpress/Give/pull/737) ([ramiy](https://github.com/ramiy))
- Remove @description tag from code documentation [\#735](https://github.com/WordImpress/Give/pull/735) ([ravinderk](https://github.com/ravinderk))
- Amount formatting [\#734](https://github.com/WordImpress/Give/pull/734) ([ravinderk](https://github.com/ravinderk))
- Few more i18n improvments [\#732](https://github.com/WordImpress/Give/pull/732) ([ramiy](https://github.com/ramiy))
- Fix code documentation [\#731](https://github.com/WordImpress/Give/pull/731) ([ravinderk](https://github.com/ravinderk))
- Fix broken plugin welcome url [\#728](https://github.com/WordImpress/Give/pull/728) ([ravinderk](https://github.com/ravinderk))
- Decrease stat if payment update from revoked/complete to cancelled [\#727](https://github.com/WordImpress/Give/pull/727) ([ravinderk](https://github.com/ravinderk))
- Remove unnecessary %s from form report detail page [\#721](https://github.com/WordImpress/Give/pull/721) ([ravinderk](https://github.com/ravinderk))
- Fix coding documentation [\#720](https://github.com/WordImpress/Give/pull/720) ([ravinderk](https://github.com/ravinderk))
- Break from foreach loop if price id found for multi donation form [\#719](https://github.com/WordImpress/Give/pull/719) ([ravinderk](https://github.com/ravinderk))
- Fix view all donation for this donor link [\#718](https://github.com/WordImpress/Give/pull/718) ([ravinderk](https://github.com/ravinderk))
- Remove current total instead of new total from old user donation stat [\#713](https://github.com/WordImpress/Give/pull/713) ([ravinderk](https://github.com/ravinderk))
- Fix code documentation [\#711](https://github.com/WordImpress/Give/pull/711) ([ravinderk](https://github.com/ravinderk))
- More i18n improvments [\#710](https://github.com/WordImpress/Give/pull/710) ([ramiy](https://github.com/ramiy))
- i18n Improvments [\#707](https://github.com/WordImpress/Give/pull/707) ([ramiy](https://github.com/ramiy))
- Fix code documentation [\#706](https://github.com/WordImpress/Give/pull/706) ([ravinderk](https://github.com/ravinderk))
- Adds "forgot password" link to login form. [\#700](https://github.com/WordImpress/Give/pull/700) ([Benunc](https://github.com/Benunc))
- add/update caribbean islands on country list [\#699](https://github.com/WordImpress/Give/pull/699) ([pryley](https://github.com/pryley))
- Fix documentation [\#695](https://github.com/WordImpress/Give/pull/695) ([ravinderk](https://github.com/ravinderk))
- Remove console.log from admin-scripts.js [\#694](https://github.com/WordImpress/Give/pull/694) ([ravinderk](https://github.com/ravinderk))
- Issues/670 [\#692](https://github.com/WordImpress/Give/pull/692) ([ravinderk](https://github.com/ravinderk))
- Unit test fixes and resolved travis-ci issues [\#838](https://github.com/WordImpress/Give/pull/838) ([DevinWalker](https://github.com/DevinWalker))
- Multiple enhancements to the donation receipt preview functionality \#274 [\#835](https://github.com/WordImpress/Give/pull/835) ([DevinWalker](https://github.com/DevinWalker))
- Add Give conditional functions [\#831](https://github.com/WordImpress/Give/pull/831) ([ramiy](https://github.com/ramiy))
- New Customer Meta Class & Table [\#830](https://github.com/WordImpress/Give/pull/830) ([DevinWalker](https://github.com/DevinWalker))
- Unify wp\_die\(\) all over the plugin. Add title and HTML status response codes. [\#827](https://github.com/WordImpress/Give/pull/827) ([ramiy](https://github.com/ramiy))
- i18n and a11y Improvments [\#819](https://github.com/WordImpress/Give/pull/819) ([ramiy](https://github.com/ramiy))
- Setting to set number of decimal points [\#817](https://github.com/WordImpress/Give/pull/817) ([ravinderk](https://github.com/ravinderk))
- issues/809 [\#815](https://github.com/WordImpress/Give/pull/815) ([ravinderk](https://github.com/ravinderk))
- Give frontend RTL [\#812](https://github.com/WordImpress/Give/pull/812) ([ramiy](https://github.com/ramiy))
- More RTL Updates [\#796](https://github.com/WordImpress/Give/pull/796) ([DevinWalker](https://github.com/DevinWalker))
- Amount field not formatting on focus out & lingering tooltip issues [\#784](https://github.com/WordImpress/Give/pull/784) ([ravinderk](https://github.com/ravinderk))
- Auto populate correct donation level when user add amount on checkout page [\#775](https://github.com/WordImpress/Give/pull/775) ([ravinderk](https://github.com/ravinderk))
- Fix smaller then million amount formatting issue [\#772](https://github.com/WordImpress/Give/pull/772) ([ravinderk](https://github.com/ravinderk))
- Issue/769 [\#771](https://github.com/WordImpress/Give/pull/771) ([DevinWalker](https://github.com/DevinWalker))
- Issue/715 [\#766](https://github.com/WordImpress/Give/pull/766) ([DevinWalker](https://github.com/DevinWalker))
- Ravinderk issues/429 - Switch a transaction to a different form  [\#761](https://github.com/WordImpress/Give/pull/761) ([DevinWalker](https://github.com/DevinWalker))
- adds form id to terms link class [\#742](https://github.com/WordImpress/Give/pull/742) ([Benunc](https://github.com/Benunc))
- Fix user lifetime donation stat  [\#716](https://github.com/WordImpress/Give/pull/716) ([ravinderk](https://github.com/ravinderk))
- Issues/702 [\#704](https://github.com/WordImpress/Give/pull/704) ([ravinderk](https://github.com/ravinderk))

## [1.5.2](https://github.com/WordImpress/Give/tree/1.5.2) (2016-07-01)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.5.1...1.5.2)

**Implemented enhancements:**

- Log for Forms Need Status Column [\#684](https://github.com/WordImpress/Give/issues/684)

**Fixed bugs:**

- Don't Display Submit button for API Tab in Settings & Improve Instructions [\#681](https://github.com/WordImpress/Give/issues/681)
- Bug when clicking on donors name from transactions list [\#680](https://github.com/WordImpress/Give/issues/680)
- give\_forms doesn't register correctly on new installs [\#671](https://github.com/WordImpress/Give/issues/671)

**Closed issues:**

- Donation amount and count get reduced when delete pending donation [\#677](https://github.com/WordImpress/Give/issues/677)

**Merged pull requests:**

- Fixed issue with API key & settings, customize description so it's be… [\#686](https://github.com/WordImpress/Give/pull/686) ([DevinWalker](https://github.com/DevinWalker))
- New status column for donation logs  [\#685](https://github.com/WordImpress/Give/pull/685) ([DevinWalker](https://github.com/DevinWalker))
- Issue/677 [\#683](https://github.com/WordImpress/Give/pull/683) ([DevinWalker](https://github.com/DevinWalker))
- Issue/671 [\#682](https://github.com/WordImpress/Give/pull/682) ([DevinWalker](https://github.com/DevinWalker))
- Modifies language to be donation-specific [\#678](https://github.com/WordImpress/Give/pull/678) ([Benunc](https://github.com/Benunc))

## [1.5.1](https://github.com/WordImpress/Give/tree/1.5.1) (2016-06-30)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.5...1.5.1)

**Merged pull requests:**

- Issue/671 [\#672](https://github.com/WordImpress/Give/pull/672) ([DevinWalker](https://github.com/DevinWalker))

## [1.5](https://github.com/WordImpress/Give/tree/1.5) (2016-06-29)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4.5...1.5)

**Implemented enhancements:**

- Agree to Terms checkbox should be on the lefthand side [\#669](https://github.com/WordImpress/Give/issues/669)
- Feature Request: Private Donation Checkbox [\#83](https://github.com/WordImpress/Give/issues/83)
- Remove use of Grunt [\#666](https://github.com/WordImpress/Give/issues/666)
- \[give\_receipt\] needs Donor Name as an attribute [\#645](https://github.com/WordImpress/Give/issues/645)
- Export additional data for donors in Reports [\#630](https://github.com/WordImpress/Give/issues/630)
- Add South Korean Won to Currencies [\#624](https://github.com/WordImpress/Give/issues/624)
- Remove the fr\_FR from the extension [\#618](https://github.com/WordImpress/Give/issues/618)
- New Payments Class [\#504](https://github.com/WordImpress/Give/issues/504)
- Make settings tabs actual tabs without reload [\#494](https://github.com/WordImpress/Give/issues/494)
- Advanced feature: Delete all test payments [\#441](https://github.com/WordImpress/Give/issues/441)
- Implement honeypot to prevent spam submissions [\#424](https://github.com/WordImpress/Give/issues/424)
- Add Date Range for Export Donation History [\#414](https://github.com/WordImpress/Give/issues/414)
- Make Payment Gateways Drag/Drop Reorderable [\#391](https://github.com/WordImpress/Give/issues/391)
- Add Moroccan currency [\#381](https://github.com/WordImpress/Give/issues/381)
- Ability to Adjust Payment Gateway Order Easily [\#276](https://github.com/WordImpress/Give/issues/276)
- Add ability to delete all test transactions [\#263](https://github.com/WordImpress/Give/issues/263)
- Show Multi-level labels on confirmation page and reports [\#175](https://github.com/WordImpress/Give/issues/175)

**Fixed bugs:**

- Duplicate "Save Settings" buttons [\#651](https://github.com/WordImpress/Give/issues/651)
- "Request Billing Information" checkbox doesn't properly override Global setting [\#649](https://github.com/WordImpress/Give/issues/649)
- Attaching a new user to a donor isn't updating [\#644](https://github.com/WordImpress/Give/issues/644)
- Need to Remove shortcode generator when visual editor disabled in user settings [\#638](https://github.com/WordImpress/Give/issues/638)
- Bug with shortcode & show\_content when two of the same forms on a page [\#634](https://github.com/WordImpress/Give/issues/634)
- Reports Custom Date Range & Refresh Reports Button Overlap  [\#626](https://github.com/WordImpress/Give/issues/626)
- give\_get\_current\_page\_url and domain mapped server with $\_SERVER\['SERVER\_PORT'\]  [\#622](https://github.com/WordImpress/Give/issues/622)
- Reports Tooltips not displaying currency properly formatted [\#620](https://github.com/WordImpress/Give/issues/620)
- Transactions Status Changes Causes Donation Form Income Amounts + Goals to Not Calculate Correctly [\#188](https://github.com/WordImpress/Give/issues/188)

**Closed issues:**

- French translation not working properly [\#525](https://github.com/WordImpress/Give/issues/525)
- Make ALL the ids be unique to avoid multiple identical ids when multiple forms are on the page [\#379](https://github.com/WordImpress/Give/issues/379)
- Support for the "Catch Evolution" theme [\#632](https://github.com/WordImpress/Give/issues/632)
- Add South African & South Korea Currency Support [\#631](https://github.com/WordImpress/Give/issues/631)
- Unit tests for reports needed [\#397](https://github.com/WordImpress/Give/issues/397)
- Automatically close Forms that reach their Goal [\#168](https://github.com/WordImpress/Give/issues/168)

**Merged pull requests:**

- Issue/666 [\#667](https://github.com/WordImpress/Give/pull/667) ([DevinWalker](https://github.com/DevinWalker))
- Honeypot field now being validated server side rather than via JS, wh… [\#665](https://github.com/WordImpress/Give/pull/665) ([DevinWalker](https://github.com/DevinWalker))
- Issue/630 [\#663](https://github.com/WordImpress/Give/pull/663) ([DevinWalker](https://github.com/DevinWalker))
- Corrects instances of "products" in comments [\#661](https://github.com/WordImpress/Give/pull/661) ([Benunc](https://github.com/Benunc))
- fixes typo in comment [\#660](https://github.com/WordImpress/Give/pull/660) ([Benunc](https://github.com/Benunc))
- Properly check for form value for offline billing field out put   htt… [\#659](https://github.com/WordImpress/Give/pull/659) ([DevinWalker](https://github.com/DevinWalker))
- Issue/175 [\#657](https://github.com/WordImpress/Give/pull/657) ([DevinWalker](https://github.com/DevinWalker))
- Issue/644 [\#656](https://github.com/WordImpress/Give/pull/656) ([DevinWalker](https://github.com/DevinWalker))
- Remove action once added to prevent incorrect output when multiple do… [\#654](https://github.com/WordImpress/Give/pull/654) ([DevinWalker](https://github.com/DevinWalker))
- Automatically close Forms that reach their Goal [\#642](https://github.com/WordImpress/Give/pull/642) ([ravinderk](https://github.com/ravinderk))
- Implement honeypot to prevent spam submissions  [\#641](https://github.com/WordImpress/Give/pull/641) ([ravinderk](https://github.com/ravinderk))
- Make Payment Gateways Drag/Drop Reorderable  [\#640](https://github.com/WordImpress/Give/pull/640) ([ravinderk](https://github.com/ravinderk))
- Adds check for visual editor [\#637](https://github.com/WordImpress/Give/pull/637) ([Benunc](https://github.com/Benunc))
- Use funciton give\_get\_errors instead edd\_get\_errors [\#635](https://github.com/WordImpress/Give/pull/635) ([ravinderk](https://github.com/ravinderk))
- Merge pull request \#628 from WordImpress/release/1.5 [\#629](https://github.com/WordImpress/Give/pull/629) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.5 [\#628](https://github.com/WordImpress/Give/pull/628) ([DevinWalker](https://github.com/DevinWalker))
- Fix bug on current\_url retrival [\#625](https://github.com/WordImpress/Give/pull/625) ([Rahe](https://github.com/Rahe))
- Properly set hover ID - it was 'earnings' when we changed it to 'inco… [\#623](https://github.com/WordImpress/Give/pull/623) ([DevinWalker](https://github.com/DevinWalker))
- Release/1.5 [\#664](https://github.com/WordImpress/Give/pull/664) ([DevinWalker](https://github.com/DevinWalker))

## [1.4.5](https://github.com/WordImpress/Give/tree/1.4.5) (2016-05-13)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4.4...1.4.5)

**Fixed bugs:**

- Bug with $\_REQUEST array key being passed causes improper min. donation calc [\#616](https://github.com/WordImpress/Give/issues/616)

**Closed issues:**

- Typo in help text [\#617](https://github.com/WordImpress/Give/issues/617)

## [1.4.4](https://github.com/WordImpress/Give/tree/1.4.4) (2016-05-13)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4.3...1.4.4)

**Fixed bugs:**

- Version 1.4.3 breaks modal popup display method [\#615](https://github.com/WordImpress/Give/issues/615)

## [1.4.3](https://github.com/WordImpress/Give/tree/1.4.3) (2016-05-12)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4.2...1.4.3)

**Implemented enhancements:**

- Add Error Reporting Level to System Report [\#605](https://github.com/WordImpress/Give/issues/605)

**Fixed bugs:**

- Error Related to minimum donation amount. [\#610](https://github.com/WordImpress/Give/issues/610)
- Run install process when network activated and new WP site is created [\#609](https://github.com/WordImpress/Give/issues/609)
- Update the email address of a customer record when the email on a user is updated [\#607](https://github.com/WordImpress/Give/issues/607)
- If Min. Donation is 0 Don't Display the Notice [\#604](https://github.com/WordImpress/Give/issues/604)
- Bug with is\_single\_price\_mode\(\) method in Give\_Donate\_Form [\#602](https://github.com/WordImpress/Give/issues/602)
- Bug with Donation History Pagination  [\#600](https://github.com/WordImpress/Give/issues/600)

**Merged pull requests:**

- Issue/609 [\#613](https://github.com/WordImpress/Give/pull/613) ([DevinWalker](https://github.com/DevinWalker))
- Issue/610 [\#612](https://github.com/WordImpress/Give/pull/612) ([DevinWalker](https://github.com/DevinWalker))
- Issue/607 [\#608](https://github.com/WordImpress/Give/pull/608) ([DevinWalker](https://github.com/DevinWalker))
- Don't display minimum amount warning if it's 0 \#604 [\#606](https://github.com/WordImpress/Give/pull/606) ([DevinWalker](https://github.com/DevinWalker))
- Issue/602 [\#603](https://github.com/WordImpress/Give/pull/603) ([DevinWalker](https://github.com/DevinWalker))
- Issue/600 [\#601](https://github.com/WordImpress/Give/pull/601) ([DevinWalker](https://github.com/DevinWalker))

## [1.4.2](https://github.com/WordImpress/Give/tree/1.4.2) (2016-04-26)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4.1...1.4.2)

**Implemented enhancements:**

- Break up readme.txt changelog into changelog.txt and add release dates [\#596](https://github.com/WordImpress/Give/issues/596)
- Remove ID column from multi-value column & Cleanup CSS [\#554](https://github.com/WordImpress/Give/issues/554)

**Fixed bugs:**

- Bug with Custom Amount Minimum due to ,/. confusion on FR locale [\#591](https://github.com/WordImpress/Give/issues/591)
- Set Email Access Token When Enabled Upon Successful Donation [\#587](https://github.com/WordImpress/Give/issues/587)
- Card number is passed to $\_SESSION with \['post\_data'\] [\#585](https://github.com/WordImpress/Give/issues/585)
- Closing Give Donation Modal Displays give-hidden classes improperly [\#582](https://github.com/WordImpress/Give/issues/582)
- Fix line breaking with animation "Updating Amount" [\#556](https://github.com/WordImpress/Give/issues/556)
- Add a cancel/back button to the optional login form [\#500](https://github.com/WordImpress/Give/issues/500)

**Closed issues:**

- Ability to Add Manual Transactions in the backend [\#163](https://github.com/WordImpress/Give/issues/163)
- Unreachable code after return statement [\#586](https://github.com/WordImpress/Give/issues/586)
- Two Functions for Getting Success Page URL [\#547](https://github.com/WordImpress/Give/issues/547)

**Merged pull requests:**

- Issue/586 [\#595](https://github.com/WordImpress/Give/pull/595) ([DevinWalker](https://github.com/DevinWalker))
- Issue/556 [\#594](https://github.com/WordImpress/Give/pull/594) ([DevinWalker](https://github.com/DevinWalker))
- Fixed bug with Custom Amount minimum and currencies with "," for deci… [\#592](https://github.com/WordImpress/Give/pull/592) ([DevinWalker](https://github.com/DevinWalker))
- Fix: Tooltips weren't loading properly when clicking the "Cancel" but… [\#589](https://github.com/WordImpress/Give/pull/589) ([DevinWalker](https://github.com/DevinWalker))
- Fixes small typo in admin ajax notice [\#584](https://github.com/WordImpress/Give/pull/584) ([MikePayne](https://github.com/MikePayne))
- Added give hidden to not method within magnific close method [\#583](https://github.com/WordImpress/Give/pull/583) ([DevinWalker](https://github.com/DevinWalker))
- Issue/554 [\#598](https://github.com/WordImpress/Give/pull/598) ([DevinWalker](https://github.com/DevinWalker))
- New changelog.txt file and added dates \#596 [\#597](https://github.com/WordImpress/Give/pull/597) ([DevinWalker](https://github.com/DevinWalker))
- Issue/587 [\#588](https://github.com/WordImpress/Give/pull/588) ([DevinWalker](https://github.com/DevinWalker))

## [1.4.1](https://github.com/WordImpress/Give/tree/1.4.1) (2016-04-12)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.4...1.4.1)

**Implemented enhancements:**

- Add DOM extension and MBString extension to System Info [\#560](https://github.com/WordImpress/Give/issues/560)
- Improve WordPress.org Screenshots [\#528](https://github.com/WordImpress/Give/issues/528)
- Added a check for DOM and MBString PHP extensions in System Info tab [\#566](https://github.com/WordImpress/Give/pull/566) ([DevinWalker](https://github.com/DevinWalker))

**Fixed bugs:**

- Some Give settings don't have defaults [\#577](https://github.com/WordImpress/Give/issues/577)
- login/cancel/register checkout form links don't trigger float-labels, and it has problems when changing gateways [\#574](https://github.com/WordImpress/Give/issues/574)
- Email Access Cookie Set Incorrectly Breaks Viewing Details [\#570](https://github.com/WordImpress/Give/issues/570)
- Validation conflicts with MemberPress [\#568](https://github.com/WordImpress/Give/issues/568)
- If no Live Transactions log drop down doesn't display properly [\#564](https://github.com/WordImpress/Give/issues/564)
- Member-only donation forms don't display member-only validation responses properly [\#551](https://github.com/WordImpress/Give/issues/551)
- Issue/551 [\#567](https://github.com/WordImpress/Give/pull/567) ([DevinWalker](https://github.com/DevinWalker))

**Closed issues:**

- Error messages don't show on `give\_send\_back\_to\_checkout\(\)` if user is not logged in [\#572](https://github.com/WordImpress/Give/issues/572)
- \[give\_receipt\] attributes needs docs [\#335](https://github.com/WordImpress/Give/issues/335)

**Merged pull requests:**

- Issue/551 [\#580](https://github.com/WordImpress/Give/pull/580) ([DevinWalker](https://github.com/DevinWalker))
- Tested \#563 and give\_set\_error works well in admin [\#579](https://github.com/WordImpress/Give/pull/579) ([DevinWalker](https://github.com/DevinWalker))
- Fix login/cancel/register checkout form link behavior [\#575](https://github.com/WordImpress/Give/pull/575) ([pryley](https://github.com/pryley))
- Add the form ID to template action hooks [\#573](https://github.com/WordImpress/Give/pull/573) ([pryley](https://github.com/pryley))
- Set appropriate cookie path using constant COOKIE\_PATH [\#571](https://github.com/WordImpress/Give/pull/571) ([DevinWalker](https://github.com/DevinWalker))
- Issue/564 [\#578](https://github.com/WordImpress/Give/pull/578) ([DevinWalker](https://github.com/DevinWalker))
- Force JavaScript validation to only look at Give forms [\#569](https://github.com/WordImpress/Give/pull/569) ([jimwebb](https://github.com/jimwebb))
- Issue/562 [\#565](https://github.com/WordImpress/Give/pull/565) ([DevinWalker](https://github.com/DevinWalker))

## [1.4](https://github.com/WordImpress/Give/tree/1.4) (2016-04-05)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.6...1.4)

**Implemented enhancements:**

- Add a link to settings page from the plugin listing page [\#531](https://github.com/WordImpress/Give/issues/531)
- Alternatives for allow\_url\_fopen or fail gracefully [\#511](https://github.com/WordImpress/Give/issues/511)
- System Info should check for ZLib, GD, and allow\_url\_fopen [\#506](https://github.com/WordImpress/Give/issues/506)
- Improve speed of Shortcode selector [\#463](https://github.com/WordImpress/Give/issues/463)
- Guest Donors should be able to access their donation history if they later create an account [\#446](https://github.com/WordImpress/Give/issues/446)
- Goal formatting is not easily filterable [\#387](https://github.com/WordImpress/Give/issues/387)
- Featured image size option for single donation forms found under Sett… [\#557](https://github.com/WordImpress/Give/pull/557) ([DevinWalker](https://github.com/DevinWalker))
- Added additional checks to the "System Info" settings tab \#506 [\#534](https://github.com/WordImpress/Give/pull/534) ([DevinWalker](https://github.com/DevinWalker))
- Added new "Goal Format" option which will allow totals to be output b… [\#533](https://github.com/WordImpress/Give/pull/533) ([DevinWalker](https://github.com/DevinWalker))
- New links on the plugin settings page Fixes \#531 [\#532](https://github.com/WordImpress/Give/pull/532) ([DevinWalker](https://github.com/DevinWalker))

**Fixed bugs:**

- When form is on the homepage it fails in various ways [\#545](https://github.com/WordImpress/Give/issues/545)
- Request Email Access link then Giving Refresh Issue [\#558](https://github.com/WordImpress/Give/issues/558)
- Email compatibility with Mandrill Regarding - HTML email problems with Mandrill [\#548](https://github.com/WordImpress/Give/issues/548)
- Issues with Gulp File Minify & Sourcemaps [\#542](https://github.com/WordImpress/Give/issues/542)
- Improve templating wrapper start and end with various themes [\#537](https://github.com/WordImpress/Give/issues/537)
- Blurry Single Donation Form Featured Image [\#535](https://github.com/WordImpress/Give/issues/535)
- Bug give\_get\_current\_page\_url\(\) $\_SERVER\["SERVER\_NAME"\] [\#530](https://github.com/WordImpress/Give/issues/530)
- Increase Modal Windows Z-Index Value [\#524](https://github.com/WordImpress/Give/issues/524)
- PHP Warning upon activation for currency setting [\#523](https://github.com/WordImpress/Give/issues/523)
- "Custom Amount Text" should not show if the field is left blank [\#522](https://github.com/WordImpress/Give/issues/522)
- Session cookies are created on every page load preventing various caches [\#521](https://github.com/WordImpress/Give/issues/521)
- Transactions with a "cancelled" status are not shown in the wp-admin Transactions table [\#514](https://github.com/WordImpress/Give/issues/514)
- Transaction Complete Issue when Creating an Account Upon Donation [\#505](https://github.com/WordImpress/Give/issues/505)
- Refresh Sessions or Login Functionality when expired sessions for receipt links & r [\#496](https://github.com/WordImpress/Give/issues/496)
- The plugin overwrites other styles and breaks them [\#466](https://github.com/WordImpress/Give/issues/466)
- Why do we have two names for the same image size? [\#412](https://github.com/WordImpress/Give/issues/412)
- Don't mess with the success page URI Fixes \#558 [\#559](https://github.com/WordImpress/Give/pull/559) ([DevinWalker](https://github.com/DevinWalker))
- New plugin-compatibility.php file [\#549](https://github.com/WordImpress/Give/pull/549) ([DevinWalker](https://github.com/DevinWalker))
- Allow for $0 set donations to be saved properly \#529 [\#540](https://github.com/WordImpress/Give/pull/540) ([DevinWalker](https://github.com/DevinWalker))
- Issue/530 [\#536](https://github.com/WordImpress/Give/pull/536) ([DevinWalker](https://github.com/DevinWalker))

**Closed issues:**

- Personalise tooltips [\#33](https://github.com/WordImpress/Give/issues/33)
- You have to \( save + reload \) after enabling a gateway in order to select it as the default [\#512](https://github.com/WordImpress/Give/issues/512)

**Merged pull requests:**

- Issue/527 [\#541](https://github.com/WordImpress/Give/pull/541) ([DevinWalker](https://github.com/DevinWalker))
- Show "cancelled" transactions on Transaction page [\#515](https://github.com/WordImpress/Give/pull/515) ([pryley](https://github.com/pryley))
- Issue/521 [\#561](https://github.com/WordImpress/Give/pull/561) ([DevinWalker](https://github.com/DevinWalker))
- Issue/496 [\#553](https://github.com/WordImpress/Give/pull/553) ([DevinWalker](https://github.com/DevinWalker))
- Issue/521 [\#552](https://github.com/WordImpress/Give/pull/552) ([DevinWalker](https://github.com/DevinWalker))
- Issue/523 [\#550](https://github.com/WordImpress/Give/pull/550) ([DevinWalker](https://github.com/DevinWalker))
- Use trailingslashit to prevent homepage redirect \#545 [\#546](https://github.com/WordImpress/Give/pull/546) ([DevinWalker](https://github.com/DevinWalker))
- Issue/542 [\#544](https://github.com/WordImpress/Give/pull/544) ([DevinWalker](https://github.com/DevinWalker))
- Issue/537 [\#539](https://github.com/WordImpress/Give/pull/539) ([DevinWalker](https://github.com/DevinWalker))
- Issue/535 [\#538](https://github.com/WordImpress/Give/pull/538) ([DevinWalker](https://github.com/DevinWalker))
- Check $\_POST for enabled gateways before $give\_options [\#519](https://github.com/WordImpress/Give/pull/519) ([pryley](https://github.com/pryley))
- Adding unit tests for the Give\_Donate\_Form class [\#517](https://github.com/WordImpress/Give/pull/517) ([cklosowski](https://github.com/cklosowski))
- Issue/412 [\#516](https://github.com/WordImpress/Give/pull/516) ([DevinWalker](https://github.com/DevinWalker))

## [1.3.6](https://github.com/WordImpress/Give/tree/1.3.6) (2016-03-09)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.5...1.3.6)

**Implemented enhancements:**

- Add deployment script to WordPress.org from Github [\#513](https://github.com/WordImpress/Give/issues/513)
- AJAX spinner animation optimization [\#508](https://github.com/WordImpress/Give/issues/508)
- Not all Legends have filters [\#487](https://github.com/WordImpress/Give/issues/487)
- Offline Donations Admin Notification Needs Better Messaging [\#448](https://github.com/WordImpress/Give/issues/448)
- Offline Donation Emails not using Give\_Emails class [\#447](https://github.com/WordImpress/Give/issues/447)
- Ability to set a minimum donation amount [\#390](https://github.com/WordImpress/Give/issues/390)
- Add Total Donations to Payment Methods Report [\#302](https://github.com/WordImpress/Give/issues/302)
- add currency sign to give\_format\_currency\(\) [\#486](https://github.com/WordImpress/Give/pull/486) ([pryley](https://github.com/pryley))
- Modified get\_earnings to filter by payment gateway in use [\#478](https://github.com/WordImpress/Give/pull/478) ([DevinWalker](https://github.com/DevinWalker))

**Fixed bugs:**

- Field Validation Needed for "Disable Guest Donations" Checkbox [\#503](https://github.com/WordImpress/Give/issues/503)
- Switching levels doesn't show the loading animation [\#510](https://github.com/WordImpress/Give/issues/510)
- Sessions should not be started in WP admin OR switch action to send\_headers [\#493](https://github.com/WordImpress/Give/issues/493)
- Update icon in dashboard widget [\#492](https://github.com/WordImpress/Give/issues/492)
- Card type image not showing when floating-labels is enabled [\#490](https://github.com/WordImpress/Give/issues/490)
- Donation total does not show the currency sign when changing custom amount [\#485](https://github.com/WordImpress/Give/issues/485)
- PHP Warning: Deprecated wp\_new\_user\_notification when donor creates a new account [\#474](https://github.com/WordImpress/Give/issues/474)
- PHP7 Constructor error [\#470](https://github.com/WordImpress/Give/issues/470)
- Revisit Floating Labels for Password & Additional Input Types [\#468](https://github.com/WordImpress/Give/issues/468)
- Required to have value in "Custom Amount Text" for field to display [\#462](https://github.com/WordImpress/Give/issues/462)
- Currency Symbols don't output reliably [\#461](https://github.com/WordImpress/Give/issues/461)
- Fix placeholder image translation string [\#450](https://github.com/WordImpress/Give/issues/450)
- "Generate API Keys" Profile setting not reflecting saved state [\#440](https://github.com/WordImpress/Give/issues/440)
- Update Transaction Payment Date not working as expected when in "Pending" status [\#435](https://github.com/WordImpress/Give/issues/435)
- Thai language doesn't output the Donation Title correctly on the Donation Receipt page [\#421](https://github.com/WordImpress/Give/issues/421)
- {receipt\_link} markup stripped in text-only emails [\#384](https://github.com/WordImpress/Give/issues/384)

**Closed issues:**

- Blank notice when updating / saving settings [\#480](https://github.com/WordImpress/Give/issues/480)
- Payment Gateway - Ability to rename gateway title [\#464](https://github.com/WordImpress/Give/issues/464)
- New Troubleshooting Give Doc Article [\#398](https://github.com/WordImpress/Give/issues/398)
- Add Spanish Translation Files [\#501](https://github.com/WordImpress/Give/issues/501)
- Include new French translation files [\#472](https://github.com/WordImpress/Give/issues/472)

**Merged pull requests:**

- Added Spanish translation files [\#502](https://github.com/WordImpress/Give/pull/502) ([DevinWalker](https://github.com/DevinWalker))
- Output URL if plain-text selected for email format [\#495](https://github.com/WordImpress/Give/pull/495) ([DevinWalker](https://github.com/DevinWalker))
- Issue/474 [\#483](https://github.com/WordImpress/Give/pull/483) ([DevinWalker](https://github.com/DevinWalker))
- Issue/448 [\#482](https://github.com/WordImpress/Give/pull/482) ([DevinWalker](https://github.com/DevinWalker))
- Added `edit\_date` true to wp\_update\_post to Fix \#435 [\#481](https://github.com/WordImpress/Give/pull/481) ([DevinWalker](https://github.com/DevinWalker))
- Issue/461 [\#477](https://github.com/WordImpress/Give/pull/477) ([DevinWalker](https://github.com/DevinWalker))
- New French translation files and updated readme Fixes \#472 [\#476](https://github.com/WordImpress/Give/pull/476) ([DevinWalker](https://github.com/DevinWalker))
- Fixes \#468 [\#475](https://github.com/WordImpress/Give/pull/475) ([DevinWalker](https://github.com/DevinWalker))
- Rename constructor method to \_\_contruct [\#460](https://github.com/WordImpress/Give/pull/460) ([lots0logs](https://github.com/lots0logs))
- Issue/500 [\#507](https://github.com/WordImpress/Give/pull/507) ([DevinWalker](https://github.com/DevinWalker))
- Geminilabs issue/490 [\#499](https://github.com/WordImpress/Give/pull/499) ([DevinWalker](https://github.com/DevinWalker))
- Geminilabs issue/390 [\#498](https://github.com/WordImpress/Give/pull/498) ([DevinWalker](https://github.com/DevinWalker))
- only start up sessions on frontend [\#497](https://github.com/WordImpress/Give/pull/497) ([DevinWalker](https://github.com/DevinWalker))
- Issue/440 [\#479](https://github.com/WordImpress/Give/pull/479) ([DevinWalker](https://github.com/DevinWalker))

## [1.3.5](https://github.com/WordImpress/Give/tree/1.3.5) (2016-01-12)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.4...1.3.5)

**Implemented enhancements:**

- Add a setting to cap total amount one donor can give per calendar year [\#322](https://github.com/WordImpress/Give/issues/322)
- Receipts Need More Details Specifically for Canadian Receipt Requirements [\#299](https://github.com/WordImpress/Give/issues/299)
- Add support for TF "Philanthropy" theme [\#454](https://github.com/WordImpress/Give/issues/454)

**Fixed bugs:**

- .mo file corruption issue with Brazilian Portuguese translation file [\#458](https://github.com/WordImpress/Give/issues/458)
- Add support for TF "Philanthropy" theme [\#454](https://github.com/WordImpress/Give/issues/454)
- Recent \[give\_receipt\] changes affecting PayPal Standard / Offsite Gateways [\#452](https://github.com/WordImpress/Give/issues/452)
- Readme issue with \<p\> tag [\#449](https://github.com/WordImpress/Give/issues/449)
- give\_receipt not passing Form title properly [\#443](https://github.com/WordImpress/Give/issues/443)
- Give Network Activated on WP Multisite Displaying upgrade messages for new sites incorrectly [\#439](https://github.com/WordImpress/Give/issues/439)
- Logged in user, different email address, new customer  [\#437](https://github.com/WordImpress/Give/issues/437)
- Test email save notification appears three times [\#364](https://github.com/WordImpress/Give/issues/364)

**Closed issues:**

- Documentation to create add-on [\#455](https://github.com/WordImpress/Give/issues/455)
- .progress-bar needs a prefix to avoid conflicts with Bootstrap [\#399](https://github.com/WordImpress/Give/issues/399)

**Merged pull requests:**

- Correct for/id relationship in credit card fields [\#459](https://github.com/WordImpress/Give/pull/459) ([joedolson](https://github.com/joedolson))
- New Network Activated site setup which runs give\_install\(\) to setup n… [\#445](https://github.com/WordImpress/Give/pull/445) ([DevinWalker](https://github.com/DevinWalker))
- Check for session purchase key before shortcode attribute [\#444](https://github.com/WordImpress/Give/pull/444) ([DevinWalker](https://github.com/DevinWalker))
- Handle lack of customer id more gracefully [\#442](https://github.com/WordImpress/Give/pull/442) ([davisshaver](https://github.com/davisshaver))
- Issue/437 [\#438](https://github.com/WordImpress/Give/pull/438) ([DevinWalker](https://github.com/DevinWalker))

## [1.3.4](https://github.com/WordImpress/Give/tree/1.3.4) (2015-12-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.3...1.3.4)

**Fixed bugs:**

- Pending "Offline Donations" payments should not be marked as abandoned [\#434](https://github.com/WordImpress/Give/issues/434)

**Merged pull requests:**

- Issue/434 [\#436](https://github.com/WordImpress/Give/pull/436) ([DevinWalker](https://github.com/DevinWalker))

## [1.3.3](https://github.com/WordImpress/Give/tree/1.3.3) (2015-12-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.2...1.3.3)

**Closed issues:**

- Ensure session IDs are a valid md5 [\#432](https://github.com/WordImpress/Give/issues/432)

**Merged pull requests:**

- Fixed vulnerability within wp-sessions.php [\#433](https://github.com/WordImpress/Give/pull/433) ([DevinWalker](https://github.com/DevinWalker))

## [1.3.2](https://github.com/WordImpress/Give/tree/1.3.2) (2015-12-12)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.1.1...1.3.2)

**Implemented enhancements:**

- Login form has a \<p\> with no text in it. It's the \<p\> for the Submit Button [\#332](https://github.com/WordImpress/Give/issues/332)
- Multi-level select field has no label [\#331](https://github.com/WordImpress/Give/issues/331)
- id="give-amount" is output twice on the page, once as an input, once as a span [\#330](https://github.com/WordImpress/Give/issues/330)
- id="give-form-{ID}"is output twice on the page, once as a div, once as the form ID [\#329](https://github.com/WordImpress/Give/issues/329)
- \<input class="give-text-input" name="give-amount"\> doesn't have a label [\#328](https://github.com/WordImpress/Give/issues/328)
- Give\_Cron class needed for upcoming recurring scheduled emails [\#422](https://github.com/WordImpress/Give/issues/422)
- Test and include new German translation files [\#383](https://github.com/WordImpress/Give/issues/383)
- Custom wrapper needed for Avada  [\#366](https://github.com/WordImpress/Give/issues/366)
- Change labels to "Donation Form" within admin [\#303](https://github.com/WordImpress/Give/issues/303)
- User link on Transactions Page Should goto Donor's Page [\#258](https://github.com/WordImpress/Give/issues/258)
- Basic form & button styles [\#234](https://github.com/WordImpress/Give/issues/234)

**Fixed bugs:**

- Divi 2.5 receipts fatal error [\#237](https://github.com/WordImpress/Give/issues/237)
- Give not calculating donation total of multi-value select properly [\#425](https://github.com/WordImpress/Give/issues/425)
- Can't get earnings for specific form \(unfinished code\) [\#408](https://github.com/WordImpress/Give/issues/408)
- HTML5 Required does not alert users to the problem on iOS devices [\#402](https://github.com/WordImpress/Give/issues/402)
- \[give\_register\] and \[give\_login\] and other shortcodes use of give\_print\_errors [\#394](https://github.com/WordImpress/Give/issues/394)
- \[give\_register\] shortcode template missing [\#393](https://github.com/WordImpress/Give/issues/393)
- CMB2 Conflict with Maps Builder [\#389](https://github.com/WordImpress/Give/issues/389)
- Donor Details dropdown is not reflecting saved value [\#388](https://github.com/WordImpress/Give/issues/388)
- North/South Korea country codes reversed [\#382](https://github.com/WordImpress/Give/issues/382)
- WordPress 4.4 bug and Give Tabs [\#377](https://github.com/WordImpress/Give/issues/377)
- Address field is not editable on Donor Screen [\#369](https://github.com/WordImpress/Give/issues/369)
- Give donation form shortcode not respecting show\_goal="false"  [\#360](https://github.com/WordImpress/Give/issues/360)
- give\_send\_back\_to\_checkout\(\) needs to take into account form ID  [\#337](https://github.com/WordImpress/Give/issues/337)

**Closed issues:**

- Conflicts with WP Geo [\#386](https://github.com/WordImpress/Give/issues/386)
- HTML5 required attribute needs to respect give\_is\_field\_required\(\) conditonal [\#361](https://github.com/WordImpress/Give/issues/361)
- id="give-amount" output multiple times on the page [\#326](https://github.com/WordImpress/Give/issues/326)
- Add new Polish translation to the Give core repository [\#418](https://github.com/WordImpress/Give/issues/418)
- Shortcode builder dialogs do not work when the "SiteOrigin Widgets Bundle" plugin is active [\#405](https://github.com/WordImpress/Give/issues/405)
- CSS conflicts with Twenty Sixteen [\#401](https://github.com/WordImpress/Give/issues/401)
- Divi and Give in modal tooltips CSS z-index conflict [\#400](https://github.com/WordImpress/Give/issues/400)
- Unit Test: Login / Register functionality [\#342](https://github.com/WordImpress/Give/issues/342)
- Unit Test: Verify constant version matches actual version [\#222](https://github.com/WordImpress/Give/issues/222)

**Merged pull requests:**

- Fixed Safari iOS bug with HTML5 required attribute and form reloading… [\#431](https://github.com/WordImpress/Give/pull/431) ([DevinWalker](https://github.com/DevinWalker))
- Fix: Discrepancies between competing metakeys causing donor details d… [\#430](https://github.com/WordImpress/Give/pull/430) ([DevinWalker](https://github.com/DevinWalker))
- Added styles to fix issues with the new twentysixteen [\#428](https://github.com/WordImpress/Give/pull/428) ([DevinWalker](https://github.com/DevinWalker))
- Issue/407 [\#427](https://github.com/WordImpress/Give/pull/427) ([DevinWalker](https://github.com/DevinWalker))
- Fixed a nasty little bug passing improper amounts most likely due to … [\#426](https://github.com/WordImpress/Give/pull/426) ([DevinWalker](https://github.com/DevinWalker))
- New Give\_Cron class added [\#423](https://github.com/WordImpress/Give/pull/423) ([DevinWalker](https://github.com/DevinWalker))
- Fixed textdomain and wrong use of escape functions [\#420](https://github.com/WordImpress/Give/pull/420) ([valeriosouza](https://github.com/valeriosouza))
- New polish translations fixes \#418 [\#419](https://github.com/WordImpress/Give/pull/419) ([DevinWalker](https://github.com/DevinWalker))
- Fixed missing earnings stats calculation [\#409](https://github.com/WordImpress/Give/pull/409) ([DevinWalker](https://github.com/DevinWalker))
- Added table\_exists method to Give\_DB class [\#404](https://github.com/WordImpress/Give/pull/404) ([ibndawood](https://github.com/ibndawood))
- Customized styles for register shortcode [\#395](https://github.com/WordImpress/Give/pull/395) ([DevinWalker](https://github.com/DevinWalker))
- Issue/389 [\#392](https://github.com/WordImpress/Give/pull/392) ([DevinWalker](https://github.com/DevinWalker))
- Updated nav wrappers \#377 [\#378](https://github.com/WordImpress/Give/pull/378) ([DevinWalker](https://github.com/DevinWalker))
- Fix check in give\_install\_roles\_on\_network [\#376](https://github.com/WordImpress/Give/pull/376) ([jimwebb](https://github.com/jimwebb))
- Fix wrong param order for \_x\(\) i18n function [\#375](https://github.com/WordImpress/Give/pull/375) ([andrejcremoznik](https://github.com/andrejcremoznik))
- Fix the broken link to github repo link [\#374](https://github.com/WordImpress/Give/pull/374) ([shivapoudel](https://github.com/shivapoudel))
- User link aka now "Donor" link now goes to donor's page \#258 [\#373](https://github.com/WordImpress/Give/pull/373) ([DevinWalker](https://github.com/DevinWalker))
- Added the word "Donation" before for the name, add new item, and edit… [\#372](https://github.com/WordImpress/Give/pull/372) ([DevinWalker](https://github.com/DevinWalker))
- Issue/369 [\#371](https://github.com/WordImpress/Give/pull/371) ([DevinWalker](https://github.com/DevinWalker))
- Issue/234 [\#367](https://github.com/WordImpress/Give/pull/367) ([DevinWalker](https://github.com/DevinWalker))
- Wrapped html5 required attributes with give\_is\_field\_required \#361 [\#363](https://github.com/WordImpress/Give/pull/363) ([DevinWalker](https://github.com/DevinWalker))
- Take into account shortcode show\_goal option when outputting goal [\#362](https://github.com/WordImpress/Give/pull/362) ([DevinWalker](https://github.com/DevinWalker))
- Added hidden label to multi-level select dropdown [\#359](https://github.com/WordImpress/Give/pull/359) ([mathetos](https://github.com/mathetos))
- Added hidden label to input.give-text-input [\#358](https://github.com/WordImpress/Give/pull/358) ([mathetos](https://github.com/mathetos))
- Swapped out "p" tags for "div's" [\#355](https://github.com/WordImpress/Give/pull/355) ([mathetos](https://github.com/mathetos))
- Changes div ID for the main content wrapper div to be "give-form-{ID}… [\#354](https://github.com/WordImpress/Give/pull/354) ([mathetos](https://github.com/mathetos))
- Edited the WooCommerce section [\#347](https://github.com/WordImpress/Give/pull/347) ([michaelbeil](https://github.com/michaelbeil))
- Restructured unit testing [\#415](https://github.com/WordImpress/Give/pull/415) ([ibndawood](https://github.com/ibndawood))
- \#405 : SiteOrigin Widgets Bundle plugin compat [\#406](https://github.com/WordImpress/Give/pull/406) ([pryley](https://github.com/pryley))

## [1.3.1.1](https://github.com/WordImpress/Give/tree/1.3.1.1) (2015-10-20)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.1...1.3.1.1)

## [1.3.1](https://github.com/WordImpress/Give/tree/1.3.1) (2015-10-19)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3.0.4...1.3.1)

**Implemented enhancements:**

- Add no-cache header to Receipt page to avoid conflict with server and browser caching [\#338](https://github.com/WordImpress/Give/issues/338)
- Checkbox for "Create Account" [\#194](https://github.com/WordImpress/Give/issues/194)
- Shortcode Attributes [\#165](https://github.com/WordImpress/Give/issues/165)
- Reporting: Bar Graph Width [\#71](https://github.com/WordImpress/Give/issues/71)
- Add html5 required attribute to required donation fields [\#346](https://github.com/WordImpress/Give/issues/346)
- New Add-on Activation Banner Class [\#316](https://github.com/WordImpress/Give/issues/316)
- Add Form Name or ID to CSV Export [\#314](https://github.com/WordImpress/Give/issues/314)
- New filter needed for multilevel donation form level text [\#307](https://github.com/WordImpress/Give/issues/307)
- New filter needed for give form classes [\#306](https://github.com/WordImpress/Give/issues/306)
- Add new filter for default form amount [\#301](https://github.com/WordImpress/Give/issues/301)
- The label for the "Transaction Types" just says "PayPal" right now. It should say "PayPal Transaction Type". [\#293](https://github.com/WordImpress/Give/issues/293)
- Prefix stats transient cache keys [\#277](https://github.com/WordImpress/Give/issues/277)
- CSS Compatibility with Jupiter Theme [\#170](https://github.com/WordImpress/Give/issues/170)
- Add no-cache headings to the Email Receipt page. [\#341](https://github.com/WordImpress/Give/pull/341) ([mathetos](https://github.com/mathetos))

**Fixed bugs:**

- Modal CC fields get squeezed and bumped to two lines [\#227](https://github.com/WordImpress/Give/issues/227)
- Fatal Error upon activation on Media Temple [\#353](https://github.com/WordImpress/Give/issues/353)
- Fix broken Travis CI build test [\#334](https://github.com/WordImpress/Give/issues/334)
- Fatal error when accessing donate page with theme that includes CMB2. [\#321](https://github.com/WordImpress/Give/issues/321)
- $0.00 Donations are going through when Display Method is "Show on Page" [\#320](https://github.com/WordImpress/Give/issues/320)
- Give admin dashicon incorrectly lights up on page load [\#315](https://github.com/WordImpress/Give/issues/315)
- Settings saved successfully admin notice not displaying [\#312](https://github.com/WordImpress/Give/issues/312)
- Multiple billing fields being output when multiple forms on a single page [\#310](https://github.com/WordImpress/Give/issues/310)
- Divi Theme and New Shortcode Builder Conflict [\#304](https://github.com/WordImpress/Give/issues/304)
- Floating Labels Get Messed Up with Multiple Donation Forms on a Single Page [\#271](https://github.com/WordImpress/Give/issues/271)
- \[give\_receipt\] attributes do not work as expected [\#267](https://github.com/WordImpress/Give/issues/267)
- Server side validation errors don't reopen modal or slide down panel  [\#264](https://github.com/WordImpress/Give/issues/264)

**Closed issues:**

- For deprecating tx [\#348](https://github.com/WordImpress/Give/issues/348)
- Support multiple currencies on site [\#344](https://github.com/WordImpress/Give/issues/344)
- Latest update reintroduced a previous bug [\#300](https://github.com/WordImpress/Give/issues/300)
- Allow thousands separator to be empty [\#213](https://github.com/WordImpress/Give/issues/213)
- More efficient script loading [\#162](https://github.com/WordImpress/Give/issues/162)
- Featured image displays in preview, but not when form inserted into a page [\#54](https://github.com/WordImpress/Give/issues/54)
- Floating Labels Not Working with CC Masking [\#273](https://github.com/WordImpress/Give/issues/273)

**Merged pull requests:**

- 😷 Remove transifex references 💀 [\#349](https://github.com/WordImpress/Give/pull/349) ([michaelbeil](https://github.com/michaelbeil))
- Issue/314 [\#345](https://github.com/WordImpress/Give/pull/345) ([DevinWalker](https://github.com/DevinWalker))
- Issue/338 [\#343](https://github.com/WordImpress/Give/pull/343) ([DevinWalker](https://github.com/DevinWalker))
- New settings\_notices method for displaying the Updated message [\#333](https://github.com/WordImpress/Give/pull/333) ([DevinWalker](https://github.com/DevinWalker))
- Checks to see if CMB2 plugin is installed first the uses included CMB… [\#327](https://github.com/WordImpress/Give/pull/327) ([DevinWalker](https://github.com/DevinWalker))
- Issue/315 [\#318](https://github.com/WordImpress/Give/pull/318) ([DevinWalker](https://github.com/DevinWalker))
- New activation banner [\#317](https://github.com/WordImpress/Give/pull/317) ([DevinWalker](https://github.com/DevinWalker))
- Fixed issue with multiple gateways [\#311](https://github.com/WordImpress/Give/pull/311) ([DevinWalker](https://github.com/DevinWalker))
- Added $level arg to new filter [\#309](https://github.com/WordImpress/Give/pull/309) ([DevinWalker](https://github.com/DevinWalker))
- `give\_form\_level\_text` filter added for altering multilevel donation … [\#308](https://github.com/WordImpress/Give/pull/308) ([DevinWalker](https://github.com/DevinWalker))
- Issue/264 [\#340](https://github.com/WordImpress/Give/pull/340) ([DevinWalker](https://github.com/DevinWalker))
- Issue/320 [\#336](https://github.com/WordImpress/Give/pull/336) ([DevinWalker](https://github.com/DevinWalker))
- Assume multiple give dropdown buttons [\#305](https://github.com/WordImpress/Give/pull/305) ([pryley](https://github.com/pryley))

## [1.3.0.4](https://github.com/WordImpress/Give/tree/1.3.0.4) (2015-10-06)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.3...1.3.0.4)

**Fixed bugs:**

- PHP 5.2 Issue with anonymous function / Closure used in new shortcode generator [\#291](https://github.com/WordImpress/Give/issues/291)
- Remove usage PHP 5.4+ bracket array declarations [\#290](https://github.com/WordImpress/Give/issues/290)
- wp\_mail function conflict with Mandrill and the new shortcode generator [\#289](https://github.com/WordImpress/Give/issues/289)
- array\_column\(\) PHP 5.5 function used  [\#288](https://github.com/WordImpress/Give/issues/288)

**Closed issues:**

- Form Contents does not show in Single Form [\#294](https://github.com/WordImpress/Give/issues/294)
- Offline Donations Gateway Needs Documentation [\#292](https://github.com/WordImpress/Give/issues/292)

**Merged pull requests:**

- Shortcode "show\_content" attribute now accepts 3 values [\#298](https://github.com/WordImpress/Give/pull/298) ([pryley](https://github.com/pryley))
- Show the shortcode button/dropdown conditionally [\#297](https://github.com/WordImpress/Give/pull/297) ([pryley](https://github.com/pryley))
- Fix: \#294 [\#296](https://github.com/WordImpress/Give/pull/296) ([pryley](https://github.com/pryley))

## [1.3](https://github.com/WordImpress/Give/tree/1.3) (2015-09-30)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.2.1...1.3)

**Implemented enhancements:**

- Salient theme wrapper fixes [\#280](https://github.com/WordImpress/Give/issues/280)
- Add Swedish Translation Files [\#238](https://github.com/WordImpress/Give/issues/238)
- Add basic table styling [\#232](https://github.com/WordImpress/Give/issues/232)
- API Enhancement: Populate Custom Meta Fields for Forms & Donations Endpoints [\#215](https://github.com/WordImpress/Give/issues/215)
- Add "Refresh Reports" button to Reports page [\#210](https://github.com/WordImpress/Give/issues/210)
- Loading scripts in footer [\#209](https://github.com/WordImpress/Give/issues/209)
- Menu section should be called "Donation Forms" [\#196](https://github.com/WordImpress/Give/issues/196)
- Fully test multisite compatibility and network settings [\#180](https://github.com/WordImpress/Give/issues/180)

**Fixed bugs:**

- Fix broken welcome screen columns [\#287](https://github.com/WordImpress/Give/issues/287)
- Salient theme wrapper fixes [\#280](https://github.com/WordImpress/Give/issues/280)
- Conditional floating labels not respecting switching payment gateways [\#278](https://github.com/WordImpress/Give/issues/278)
- Floating Labels Clashing with Custom Fields [\#272](https://github.com/WordImpress/Give/issues/272)
- Stripe floating label is undefined [\#270](https://github.com/WordImpress/Give/issues/270)
- Norwegian Kroner Not Showing [\#265](https://github.com/WordImpress/Give/issues/265)
- Unit Tests Error: Fatal error: Call to undefined function is\_post\_type\_viewable\(\) in /tmp/wordpress-tests-lib/includes/utils.php on line 365 [\#255](https://github.com/WordImpress/Give/issues/255)
- Multiple Give Forms on Single Page Causes CC Validation to Fail [\#254](https://github.com/WordImpress/Give/issues/254)
- When Only a Payment Gateway without CC Fields is Active Fatal JS error [\#253](https://github.com/WordImpress/Give/issues/253)
- `give\_require\_billing\_address` filter is always set to `false` [\#249](https://github.com/WordImpress/Give/issues/249)
- Reports bar graphs incorrectly overlaid on eachother [\#248](https://github.com/WordImpress/Give/issues/248)
- Reports JS Uncaught Error: Time mode requires the flot.time plugin. [\#246](https://github.com/WordImpress/Give/issues/246)
- Include CMB2CSS in the head to avoid FOUC [\#243](https://github.com/WordImpress/Give/issues/243)
- Test Mode Active notification displays for editors when inactive [\#242](https://github.com/WordImpress/Give/issues/242)
- Custom amount field needs to display numbers by default on mobile/tablet [\#233](https://github.com/WordImpress/Give/issues/233)
- Modal has no close button [\#228](https://github.com/WordImpress/Give/issues/228)
- Custom Amount label in PayPal Standard is wrong [\#212](https://github.com/WordImpress/Give/issues/212)
- Licenses are not deleted when deactivated [\#203](https://github.com/WordImpress/Give/issues/203)
- Form Content Wrap doesn't go full width without Featured Image [\#192](https://github.com/WordImpress/Give/issues/192)
- Multiple donations don't display in reports or on the forms fields [\#85](https://github.com/WordImpress/Give/issues/85)

**Closed issues:**

- Adding donation area below the content of every post? [\#286](https://github.com/WordImpress/Give/issues/286)
- Purchase confirmation? [\#285](https://github.com/WordImpress/Give/issues/285)
- Where is the shopping cart? [\#284](https://github.com/WordImpress/Give/issues/284)
- Removing addon and then adding same addon bug? [\#283](https://github.com/WordImpress/Give/issues/283)
- Shortcodes not outputting properly on page [\#279](https://github.com/WordImpress/Give/issues/279)
- Change the ajax loader from "Updating Price" to "Updating Amount" [\#269](https://github.com/WordImpress/Give/issues/269)
- Documentation: New Give Goals shortcode [\#261](https://github.com/WordImpress/Give/issues/261)
- Admin Column: If no goal is set for form show "n/a" rather than "0.00" [\#256](https://github.com/WordImpress/Give/issues/256)
- Session Issues with Pantheon and Possibly PHP 5.6+ [\#218](https://github.com/WordImpress/Give/issues/218)
- Create Shortcode for Goals [\#184](https://github.com/WordImpress/Give/issues/184)

**Merged pull requests:**

- Hotfix/fix floatlabel on events [\#282](https://github.com/WordImpress/Give/pull/282) ([pryley](https://github.com/pryley))
- Feature: 1. Form-specific float-labels option / 2. Extended shortcode attributes [\#275](https://github.com/WordImpress/Give/pull/275) ([pryley](https://github.com/pryley))
- Refresh button for Issue/210 [\#266](https://github.com/WordImpress/Give/pull/266) ([DevinWalker](https://github.com/DevinWalker))
- Fix for Issue/249 [\#262](https://github.com/WordImpress/Give/pull/262) ([DevinWalker](https://github.com/DevinWalker))
- Issue/184 [\#260](https://github.com/WordImpress/Give/pull/260) ([DevinWalker](https://github.com/DevinWalker))
- Issue/188 [\#259](https://github.com/WordImpress/Give/pull/259) ([DevinWalker](https://github.com/DevinWalker))
- Check if form has goal for admin column [\#257](https://github.com/WordImpress/Give/pull/257) ([DevinWalker](https://github.com/DevinWalker))
- Issue/203 [\#252](https://github.com/WordImpress/Give/pull/252) ([DevinWalker](https://github.com/DevinWalker))
- Re: \#249 [\#251](https://github.com/WordImpress/Give/pull/251) ([pryley](https://github.com/pryley))
- Fix bar order in report graph: \#248 [\#250](https://github.com/WordImpress/Give/pull/250) ([pryley](https://github.com/pryley))
- Fixes: \#246 [\#247](https://github.com/WordImpress/Give/pull/247) ([pryley](https://github.com/pryley))
- Create give-sv\_SE.mo [\#241](https://github.com/WordImpress/Give/pull/241) ([mepmepmep](https://github.com/mepmepmep))
- Create give-sv\_SE.po [\#240](https://github.com/WordImpress/Give/pull/240) ([mepmepmep](https://github.com/mepmepmep))
- Feature/bower [\#236](https://github.com/WordImpress/Give/pull/236) ([pryley](https://github.com/pryley))
- bump float-labels.js to v1.0.4 [\#235](https://github.com/WordImpress/Give/pull/235) ([pryley](https://github.com/pryley))
- Feature/floatlabels \#204 [\#231](https://github.com/WordImpress/Give/pull/231) ([pryley](https://github.com/pryley))
- Added option to disable single give forms sidebar [\#230](https://github.com/WordImpress/Give/pull/230) ([DevinWalker](https://github.com/DevinWalker))
- New option to load scripts in the footer Fixes \#209 [\#229](https://github.com/WordImpress/Give/pull/229) ([DevinWalker](https://github.com/DevinWalker))

## [1.2.1](https://github.com/WordImpress/Give/tree/1.2.1) (2015-09-02)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.2...1.2.1)

**Implemented enhancements:**

- UX Improvement for viewing Donations Details [\#216](https://github.com/WordImpress/Give/issues/216)
- Success Page Fallback [\#214](https://github.com/WordImpress/Give/issues/214)
- Status change options on the history screen [\#34](https://github.com/WordImpress/Give/issues/34)
- Custom Slug [\#27](https://github.com/WordImpress/Give/issues/27)

**Fixed bugs:**

- Donation History Shortcode Error CSS Hidden by Default [\#225](https://github.com/WordImpress/Give/issues/225)
- PayPal Standard back button issue [\#221](https://github.com/WordImpress/Give/issues/221)
- Modal loads ALL form data [\#217](https://github.com/WordImpress/Give/issues/217)

**Closed issues:**

- Add new button notice tooltip [\#200](https://github.com/WordImpress/Give/issues/200)
- Template styles [\#115](https://github.com/WordImpress/Give/issues/115)
- Per-form email settings [\#39](https://github.com/WordImpress/Give/issues/39)

**Merged pull requests:**

- Fixed \#216 [\#224](https://github.com/WordImpress/Give/pull/224) ([DevinWalker](https://github.com/DevinWalker))
- Issue/214 [\#223](https://github.com/WordImpress/Give/pull/223) ([DevinWalker](https://github.com/DevinWalker))

## [1.2](https://github.com/WordImpress/Give/tree/1.2) (2015-09-01)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.1...1.2)

**Implemented enhancements:**

- trigger JS events after any ajax requests that change the form DOM [\#204](https://github.com/WordImpress/Give/issues/204)
- Spinner should be an icon font [\#193](https://github.com/WordImpress/Give/issues/193)
- Custom donation [\#110](https://github.com/WordImpress/Give/issues/110)
- Input mask for credit card fields [\#76](https://github.com/WordImpress/Give/issues/76)

**Fixed bugs:**

- `give\_format\_amount` method applied twice [\#208](https://github.com/WordImpress/Give/issues/208)
- Goal amount not respecting thousands separator [\#205](https://github.com/WordImpress/Give/issues/205)
- GIVE\_SLUG define doesn't affect slug [\#199](https://github.com/WordImpress/Give/issues/199)
- Fix widget tooltips [\#195](https://github.com/WordImpress/Give/issues/195)
- Addons page PHP Warnings [\#191](https://github.com/WordImpress/Give/issues/191)
- Form title not displayed on PayPal Standard  [\#190](https://github.com/WordImpress/Give/issues/190)
- Polish and verify give\_is\_admin\_page\(\) new function [\#187](https://github.com/WordImpress/Give/issues/187)

**Closed issues:**

- Broken SSL padlock! [\#220](https://github.com/WordImpress/Give/issues/220)
- New map post title while map builder modal customizer is enabled by default [\#202](https://github.com/WordImpress/Give/issues/202)
- Add new Maps Builder button notice tooltip [\#201](https://github.com/WordImpress/Give/issues/201)
- Creation of Give Pages [\#197](https://github.com/WordImpress/Give/issues/197)
- GoDaddy Windows Plesk Fatal Error [\#183](https://github.com/WordImpress/Give/issues/183)

**Merged pull requests:**

- Use accounting.js for formatting custom donation [\#219](https://github.com/WordImpress/Give/pull/219) ([helgatheviking](https://github.com/helgatheviking))
- Extensibility improvements [\#211](https://github.com/WordImpress/Give/pull/211) ([cwackerman](https://github.com/cwackerman))
- Input mask for credit card fields - fixes \#76 [\#206](https://github.com/WordImpress/Give/pull/206) ([pryley](https://github.com/pryley))

## [1.1](https://github.com/WordImpress/Give/tree/1.1) (2015-07-22)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.0.1...1.1)

**Implemented enhancements:**

- Test Mode Admin Notification Improvements [\#174](https://github.com/WordImpress/Give/issues/174)
- API Integration [\#172](https://github.com/WordImpress/Give/issues/172)
- PalPal Standard - Display Multi-Level Select Option [\#164](https://github.com/WordImpress/Give/issues/164)
- Optimization: Combine ALL js and CSS when SCRIPT\_DEBUG turned off [\#64](https://github.com/WordImpress/Give/issues/64)
- Offline Payment: Enhance with Donation Pending Email w/ Instructions [\#19](https://github.com/WordImpress/Give/issues/19)

**Fixed bugs:**

- Font icons are block with SSL and ugly permalinks [\#182](https://github.com/WordImpress/Give/issues/182)
- Fix Broken Donor Links [\#179](https://github.com/WordImpress/Give/issues/179)
- Fatal Error on Give\_DB\_Customers [\#171](https://github.com/WordImpress/Give/issues/171)

**Closed issues:**

- Transaction Detail page has "Customer" language [\#177](https://github.com/WordImpress/Give/issues/177)

**Merged pull requests:**

- Change rendered text Customer to Donor [\#178](https://github.com/WordImpress/Give/pull/178) ([topher1kenobe](https://github.com/topher1kenobe))

## [1.0.1](https://github.com/WordImpress/Give/tree/1.0.1) (2015-07-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.0...1.0.1)

## [1.0](https://github.com/WordImpress/Give/tree/1.0) (2015-07-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/1.0.0...1.0)

## [1.0.0](https://github.com/WordImpress/Give/tree/1.0.0) (2015-07-14)
[Full Changelog](https://github.com/WordImpress/Give/compare/0.9.5.1...1.0.0)

**Implemented enhancements:**

- Improve give\_is\_admin\_page\(\) function [\#166](https://github.com/WordImpress/Give/issues/166)

**Fixed bugs:**

- Donor Log link goes to All logs, not filtered view of log [\#167](https://github.com/WordImpress/Give/issues/167)
- Translations don't seem to take [\#161](https://github.com/WordImpress/Give/issues/161)
- PHP notice [\#159](https://github.com/WordImpress/Give/issues/159)
- Not Obvious that Custom Text is Required [\#158](https://github.com/WordImpress/Give/issues/158)
- Divi Theme: Fix template not respecting container [\#157](https://github.com/WordImpress/Give/issues/157)
- \[give\_receipt\] and Divi Page Builder  [\#156](https://github.com/WordImpress/Give/issues/156)
- Modal Window Login Issue [\#155](https://github.com/WordImpress/Give/issues/155)
- Microdata added twice to titles [\#154](https://github.com/WordImpress/Give/issues/154)
- Warning: is\_readable\(\) \[function.is-readable\]: open\_basedir restriction in effect.  [\#82](https://github.com/WordImpress/Give/issues/82)

**Closed issues:**

- Feature Request: Reoccurring donations \(subscriptions\) [\#169](https://github.com/WordImpress/Give/issues/169)
- Registration form on lightbox appearance [\#60](https://github.com/WordImpress/Give/issues/60)

**Merged pull requests:**

- fix PHP notice for Undefined index: \_give\_price. closes \#159. [\#160](https://github.com/WordImpress/Give/pull/160) ([helgatheviking](https://github.com/helgatheviking))

## [0.9.5.1](https://github.com/WordImpress/Give/tree/0.9.5.1) (2015-06-05)
[Full Changelog](https://github.com/WordImpress/Give/compare/0.9.5...0.9.5.1)

## [0.9.5](https://github.com/WordImpress/Give/tree/0.9.5) (2015-06-04)
**Implemented enhancements:**

- Composer package [\#131](https://github.com/WordImpress/Give/issues/131)
- Shortcode: Add support for \[give\_profile\_editor\]  [\#130](https://github.com/WordImpress/Give/issues/130)
- Offline Donation Enhancements [\#124](https://github.com/WordImpress/Give/issues/124)
- PayPal Standard: Allow Option to Switch from Donations to Standard Purchase [\#121](https://github.com/WordImpress/Give/issues/121)
- Give - Brazilian Portuguese Translation [\#107](https://github.com/WordImpress/Give/issues/107)
- Sending test email doesn't notify user that anything was sent [\#102](https://github.com/WordImpress/Give/issues/102)
- Apply donation for [\#81](https://github.com/WordImpress/Give/issues/81)
- Stripe live\_publishable\_key error [\#75](https://github.com/WordImpress/Give/issues/75)
- Reporting: Switch from Line to Bar graphs  [\#66](https://github.com/WordImpress/Give/issues/66)
- Support multiple colors. [\#58](https://github.com/WordImpress/Give/issues/58)
- When "custom donation" is enabled it's not obvious to the end user [\#53](https://github.com/WordImpress/Give/issues/53)
- New Form:  Is Excerpt used?  [\#51](https://github.com/WordImpress/Give/issues/51)
- Custom Amount Validation: Do not allow $0.00 amount to open modal or slide down [\#44](https://github.com/WordImpress/Give/issues/44)
- Custom Amount Language [\#43](https://github.com/WordImpress/Give/issues/43)
- Consistency in Language [\#41](https://github.com/WordImpress/Give/issues/41)
- AddOn for Events [\#28](https://github.com/WordImpress/Give/issues/28)
- Offline Donation Enhancement [\#26](https://github.com/WordImpress/Give/issues/26)
- Custom Amount [\#23](https://github.com/WordImpress/Give/issues/23)
- Add Setting to disable single posts [\#18](https://github.com/WordImpress/Give/issues/18)
- Goals [\#42](https://github.com/WordImpress/Give/issues/42)

**Fixed bugs:**

- Reports: Filtering by "Last Month" incorrect url "&"  [\#149](https://github.com/WordImpress/Give/issues/149)
- Give Sidebar displays shop sidebar [\#140](https://github.com/WordImpress/Give/issues/140)
- Dollar sign in modal doesn't always work [\#120](https://github.com/WordImpress/Give/issues/120)
- Welcome Screen CSS Alignment off [\#119](https://github.com/WordImpress/Give/issues/119)
- Permissions Bug: edit\_give\_payments preventing deletion of transactions [\#118](https://github.com/WordImpress/Give/issues/118)
- Orphans [\#114](https://github.com/WordImpress/Give/issues/114)
- Emails: There was an error retrieving this donation title [\#106](https://github.com/WordImpress/Give/issues/106)
- Donation amount not restored when clicking option after selecting custom amount [\#88](https://github.com/WordImpress/Give/issues/88)
- Setting Section Title Not Displaying Proper Text [\#87](https://github.com/WordImpress/Give/issues/87)
- Global vs Form Payment Gateways  [\#86](https://github.com/WordImpress/Give/issues/86)
- WordPress.org - Unexpected Output Error [\#84](https://github.com/WordImpress/Give/issues/84)
- Plugin could not be activated because it triggered a fatal error \(/libraries/cmb2/\) [\#80](https://github.com/WordImpress/Give/issues/80)
- Dashboard capabilities error [\#73](https://github.com/WordImpress/Give/issues/73)
- Admin Form Creation: Custom Amount Text Field Won't Accept Empty Value [\#72](https://github.com/WordImpress/Give/issues/72)
- Shortcodes don't output in the content [\#68](https://github.com/WordImpress/Give/issues/68)
- Content shouldn't be in the modal [\#67](https://github.com/WordImpress/Give/issues/67)
- Admin: Multi-Level Amount Fields Not Passed through give\_format\_amount\(\) [\#65](https://github.com/WordImpress/Give/issues/65)
- Undefined Index "page\_success" when Page is null in settings [\#62](https://github.com/WordImpress/Give/issues/62)
- Getting started typo [\#61](https://github.com/WordImpress/Give/issues/61)
- Optional login is not optional [\#59](https://github.com/WordImpress/Give/issues/59)
- Template System: Not Respecting Some Theme Containers [\#55](https://github.com/WordImpress/Give/issues/55)
- Custom Amount Validation: Do not allow $0.00 amount to open modal or slide down [\#44](https://github.com/WordImpress/Give/issues/44)
- Installation: Default Settings Not Saving Properly [\#40](https://github.com/WordImpress/Give/issues/40)
- Incorrect donation notification - or possible email settings issue [\#38](https://github.com/WordImpress/Give/issues/38)
- Various fails with out-of-the-box settings [\#32](https://github.com/WordImpress/Give/issues/32)
- Fatal error on list forms screen [\#31](https://github.com/WordImpress/Give/issues/31)
- Odd donation total formatting [\#30](https://github.com/WordImpress/Give/issues/30)
- Warnings on forms with out-of-the-box settings [\#29](https://github.com/WordImpress/Give/issues/29)
- Offline Donation Customization [\#24](https://github.com/WordImpress/Give/issues/24)
- Welcome Screen Should always Goto About Give [\#22](https://github.com/WordImpress/Give/issues/22)
- Donations over $999.99 [\#21](https://github.com/WordImpress/Give/issues/21)
- Unsaved changes prompt on publishing a new form [\#20](https://github.com/WordImpress/Give/issues/20)
- Checks Gateway Not Working Properly [\#16](https://github.com/WordImpress/Give/issues/16)
- Metabox order [\#15](https://github.com/WordImpress/Give/issues/15)
- Broken Column Links to Reports [\#14](https://github.com/WordImpress/Give/issues/14)
- Fix Gulp Sourcemaps Generation Bug [\#10](https://github.com/WordImpress/Give/issues/10)
- Dashboard Widget: PHP Warning with date  [\#9](https://github.com/WordImpress/Give/issues/9)
- Logs: Fix Dropdown Filter [\#8](https://github.com/WordImpress/Give/issues/8)
- Admin-footer.php Fatal Error [\#7](https://github.com/WordImpress/Give/issues/7)
- No Editor Toolbar in Visual Mode [\#6](https://github.com/WordImpress/Give/issues/6)
- Add New Form Category link doesn't work [\#5](https://github.com/WordImpress/Give/issues/5)
- Yet Another Bug [\#2](https://github.com/WordImpress/Give/issues/2)
- Test Bug [\#1](https://github.com/WordImpress/Give/issues/1)
- Single Form image displays featured image using incorrect size [\#152](https://github.com/WordImpress/Give/issues/152)
- Column Bug: Multi-level values incorrect [\#151](https://github.com/WordImpress/Give/issues/151)
- Currency Separator Calculations Bug [\#150](https://github.com/WordImpress/Give/issues/150)
- Conflict between Related Posts plugin and Give [\#129](https://github.com/WordImpress/Give/issues/129)

**Closed issues:**

- Decrease required PHP version in composer.json [\#147](https://github.com/WordImpress/Give/issues/147)
- Add CONTRIBUTING.md to README.md [\#145](https://github.com/WordImpress/Give/issues/145)
- Coding standards [\#142](https://github.com/WordImpress/Give/issues/142)
- Tag releases [\#133](https://github.com/WordImpress/Give/issues/133)
- Contributions [\#132](https://github.com/WordImpress/Give/issues/132)
- Fix Broken Icon Image for SSL Secure Sites [\#128](https://github.com/WordImpress/Give/issues/128)
- CSS Issue: Search Box under Reports \> Logs \> API Requests [\#127](https://github.com/WordImpress/Give/issues/127)
- Jetpack style for activating addons? [\#117](https://github.com/WordImpress/Give/issues/117)
- Import/Export [\#116](https://github.com/WordImpress/Give/issues/116)
- TinyMCE icon? [\#113](https://github.com/WordImpress/Give/issues/113)
- New top level pages.. [\#112](https://github.com/WordImpress/Give/issues/112)
- To include a picture. [\#111](https://github.com/WordImpress/Give/issues/111)
- Prefix .icon class to prevent conflicts [\#103](https://github.com/WordImpress/Give/issues/103)
- {name} isn't correctly rendered in test email [\#100](https://github.com/WordImpress/Give/issues/100)
- When exporting a report, apostrophe's are not correctly shown [\#96](https://github.com/WordImpress/Give/issues/96)
- Inconsistent UI when switching Report types [\#95](https://github.com/WordImpress/Give/issues/95)
- PHP warning when exporting PDF [\#93](https://github.com/WordImpress/Give/issues/93)
- Property of non-object on Forms Report [\#91](https://github.com/WordImpress/Give/issues/91)
-  PHP Notice:  Undefined variable: unlimited [\#89](https://github.com/WordImpress/Give/issues/89)
- Setting - General Tab: Form fields [\#57](https://github.com/WordImpress/Give/issues/57)
- 5 star rating opens bad link [\#56](https://github.com/WordImpress/Give/issues/56)
- Improve "Register / Login Form" description [\#50](https://github.com/WordImpress/Give/issues/50)
- Improve "Default Gateway" description [\#49](https://github.com/WordImpress/Give/issues/49)
- Improve "Payment Fields" description [\#48](https://github.com/WordImpress/Give/issues/48)
- Improve "Display Content" description [\#47](https://github.com/WordImpress/Give/issues/47)
- Improve "Display Style" description [\#46](https://github.com/WordImpress/Give/issues/46)
- Improve description of one donation price vs multiple levels [\#45](https://github.com/WordImpress/Give/issues/45)
- Split \(aka A/B\) testing [\#37](https://github.com/WordImpress/Give/issues/37)
- \[UK-specific\] Gift Aid extension [\#36](https://github.com/WordImpress/Give/issues/36)
- Add ability to toggle on/off stylesheet [\#13](https://github.com/WordImpress/Give/issues/13)
- Write "Getting Started" content [\#12](https://github.com/WordImpress/Give/issues/12)
- Add an Add-ons Tab to Welcome page [\#11](https://github.com/WordImpress/Give/issues/11)
- Auto Scroll [\#4](https://github.com/WordImpress/Give/issues/4)
- Feature Request [\#3](https://github.com/WordImpress/Give/issues/3)
- Need to specify .mpf-content more specifically to avoid conflicts [\#141](https://github.com/WordImpress/Give/issues/141)

**Merged pull requests:**

- Update featured-image.php [\#153](https://github.com/WordImpress/Give/pull/153) ([ibndawood](https://github.com/ibndawood))
- Decrease required PHP version in composer.json [\#148](https://github.com/WordImpress/Give/pull/148) ([michaelbeil](https://github.com/michaelbeil))
- Add CONTRIBUTING.md to README.md [\#146](https://github.com/WordImpress/Give/pull/146) ([michaelbeil](https://github.com/michaelbeil))
- Inline docs edits [\#144](https://github.com/WordImpress/Give/pull/144) ([michaelbeil](https://github.com/michaelbeil))
- Coding standards [\#143](https://github.com/WordImpress/Give/pull/143) ([michaelbeil](https://github.com/michaelbeil))
- Mergin to Goals branch. Implements \#42 [\#139](https://github.com/WordImpress/Give/pull/139) ([ibndawood](https://github.com/ibndawood))
- Added Donation Limiting for Logged-In Users. [\#138](https://github.com/WordImpress/Give/pull/138) ([d4mation](https://github.com/d4mation))
- Composerify [\#135](https://github.com/WordImpress/Give/pull/135) ([michaelbeil](https://github.com/michaelbeil))
- Guidlines for contributing in CONTRIBUTING.md [\#134](https://github.com/WordImpress/Give/pull/134) ([michaelbeil](https://github.com/michaelbeil))
- Admin notice when test email sent  [\#126](https://github.com/WordImpress/Give/pull/126) ([NikV](https://github.com/NikV))
- inline Docs for /forms/template.php and /single-give-form/featured-image.php [\#122](https://github.com/WordImpress/Give/pull/122) ([NikV](https://github.com/NikV))
- New action - give\_payment\_receipt\_before\_table [\#109](https://github.com/WordImpress/Give/pull/109) ([cryptoapi](https://github.com/cryptoapi))
- Give - Brazilian Portuguese Translation "pt\_BR" [\#108](https://github.com/WordImpress/Give/pull/108) ([monecchi](https://github.com/monecchi))
- Update README.md [\#105](https://github.com/WordImpress/Give/pull/105) ([chriscct7](https://github.com/chriscct7))
- Resubmit https://github.com/WordImpress/Give/pull/99 [\#104](https://github.com/WordImpress/Give/pull/104) ([chriscct7](https://github.com/chriscct7))
- Fixes \#100 [\#101](https://github.com/WordImpress/Give/pull/101) ([amdrew](https://github.com/amdrew))
- Fixes \#96 [\#97](https://github.com/WordImpress/Give/pull/97) ([amdrew](https://github.com/amdrew))
- Fix \#93 [\#94](https://github.com/WordImpress/Give/pull/94) ([amdrew](https://github.com/amdrew))
- Check that categories are returned and are not a WP Error. \#91 [\#92](https://github.com/WordImpress/Give/pull/92) ([pippinsplugins](https://github.com/pippinsplugins))
- Fixes \#89 [\#90](https://github.com/WordImpress/Give/pull/90) ([amdrew](https://github.com/amdrew))
- Release/0.8 [\#70](https://github.com/WordImpress/Give/pull/70) ([DevinWalker](https://github.com/DevinWalker))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*