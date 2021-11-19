<?php

// same as default WP from wp-admin/admin-header.php.
$wp_version_class = 'branch-' . str_replace(['.', ','], '-', floatval(get_bloginfo('version')));

set_current_screen();
?>

<!DOCTYPE html>
<html <?php
language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php
        esc_html_e('GiveWP &rsaquo; Onboarding Wizard', 'give'); ?></title>
    <?php
    do_action('admin_enqueue_scripts'); ?>
    <?php
    do_action('admin_print_styles'); ?>
    <?php
    do_action('admin_print_scripts'); ?>
    <?php
    do_action('admin_head'); ?>
</head>
<body class="<?php
echo esc_attr($wp_version_class); ?>">
<div id="onboarding-wizard-app"></div>
<?php
do_action('admin_print_footer_scripts'); ?>
</body>
</html>
