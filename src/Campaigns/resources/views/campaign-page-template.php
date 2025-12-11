<?php

use Give\Framework\Views\View;

$template_html = do_blocks(View::load('Campaigns.campaign-page-content'));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php echo $template_html; ?>

<?php wp_footer(); ?>
</body>
</html>
