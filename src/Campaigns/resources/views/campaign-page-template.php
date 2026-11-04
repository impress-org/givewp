<?php

/**
 * @since TBD Rename $template_html to $give_template_html so the template variable has the Give prefix.
 * @since 4.19.1 Escape output.
 */

use Give\Framework\Views\View;

if (!defined('ABSPATH')) {
    exit;
}

$give_template_html = do_blocks(View::load('Campaigns.campaign-page-content'));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputs the complete page document built from escaped blocks via do_blocks().
echo $give_template_html; ?>

<?php wp_footer(); ?>
</body>
</html>
