<?php
/**
 * Offsite payment gateway Iframe redirect handler view.
 *
 * @since TBD Escape output, including translated strings.
 * @since 2.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/* @var string $location Payment gateway checkout page url. */
?>
<!DOCTYPE html>
<html <?php
language_attributes(); ?>>
<head>
    <meta charset="utf-8">
    <title><?php
        esc_html_e('Redirecting...', 'give'); ?></title>
</head>
<body>
<a style="font-size: 0" id="link" href="<?php
echo esc_url($location); ?>" target="_parent"></a>
<script>
    document.getElementById('link').click();
</script>
</body>
</html>
