<?php
/**
 * Offsite payment gateway Iframe redirect handler view.
 *
 * @since 2.7.0
 */

/* @var string $location Payment gateway checkout page url. */
?>
<!DOCTYPE html>
<html <?php
language_attributes(); ?>>
<head>
    <meta charset="utf-8">
    <title><?php
        _e('Redirecting...', 'give'); ?></title>
</head>
<body>
<a style="font-size: 0" id="link" href="<?php
echo $location; ?>" target="_parent"></a>
<script>
    document.getElementById('link').click();
</script>
</body>
</html>
