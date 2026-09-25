<?php /** @since TBD Escape output. */ ?>
:root {
    --give-primary-color: <?= esc_attr($primaryColor) ?>;
    --give-header-background-image: url("<?= esc_url_raw($headerBackgroundImage) ?>");
    <?php if (!empty($headerBackgroundColor)) : ?>
    --give-header-background-color--for-rgb: <?= (int) hexdec(substr($headerBackgroundColor, 1, 2)) ?>, <?= (int) hexdec(substr($headerBackgroundColor, 3, 2)) ?>, <?= (int) hexdec(substr($headerBackgroundColor, 5, 2)) ?>;
    <?php endif; ?>
    --give-header-stats-progressbar-color: <?= sanitize_hex_color($statsProgressBarColor) ?? '' ?>;
    --give-primary-font: '<?= esc_attr($primaryFont); ?>';
}
