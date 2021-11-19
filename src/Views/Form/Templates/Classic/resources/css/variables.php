:root {
    --give-primary-color: <?= $primaryColor ?>;
    --give-primary-color--for-rgb: <?= hexdec(substr($primaryColor, 1, 2)) ?>, <?= hexdec(substr($primaryColor, 3, 2)) ?>, <?= hexdec(substr($primaryColor, 5, 2)) ?>;
    --give-header-background-image: url("<?= $headerBackgroundImage ?>");
}
