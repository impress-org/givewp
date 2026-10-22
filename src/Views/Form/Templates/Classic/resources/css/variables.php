<?php /** @since TBD Escape output and replace short echo tags with escaped echo. */ ?>
:root {
    --give-primary-color: <?php echo esc_attr($primaryColor); ?>;
    --give-header-background-image: url("<?php echo esc_url_raw($headerBackgroundImage); ?>");
    <?php if (!empty($headerBackgroundColor)) : ?>
    --give-header-background-color--for-rgb: <?php echo (int) hexdec(substr($headerBackgroundColor, 1, 2)); ?>, <?php echo (int) hexdec(substr($headerBackgroundColor, 3, 2)); ?>, <?php echo (int) hexdec(substr($headerBackgroundColor, 5, 2)); ?>;
    <?php endif; ?>
    --give-header-stats-progressbar-color: <?php echo sanitize_hex_color($statsProgressBarColor) ?? ''; ?>;
    --give-primary-font: '<?php echo esc_attr($primaryFont); ?>';
}
