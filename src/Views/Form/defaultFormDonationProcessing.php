<?php
/**
 * Payment confirmation view.
 *
 * @since TBD Escape output.
 * @since 2.7.0
 */

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
<script type="text/javascript">
    setTimeout(function() {
        window.location = <?php echo wp_json_encode(esc_url_raw(give_get_success_page_uri()), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    }, 5000);
</script>
</body>
</html>
