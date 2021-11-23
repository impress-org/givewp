<?php
/**
 * Payment confirmation view.
 *
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
        window.location = '<?php echo give_get_success_page_uri(); ?>';
    }, 5000);
</script>
</body>
</html>
