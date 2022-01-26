:root {
    --give-primary-color: <?= $primaryColor ?>;
    --give-header-background-image: url("<?= $headerBackgroundImage ?>");
    <?php if (!empty($headerBackgroundColor)) : ?>
    --give-header-background-color--for-rgb: <?= hexdec(substr($headerBackgroundColor, 1, 2)) ?>, <?= hexdec(substr($headerBackgroundColor, 3, 2)) ?>, <?= hexdec(substr($headerBackgroundColor, 5, 2)) ?>;
    <?php endif; ?>
    --give-header-stats-progressbar-color: <?= $statsProgressBarColor ?>;
    --give-primary-font: '<?= $primaryFont; ?>';
}
