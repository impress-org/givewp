<?php

// same as default WP from wp-admin/admin-header.php.
$wp_version_class = 'branch-' . str_replace(['.', ','], '-', floatval(get_bloginfo('version')));

set_current_screen();
?>

<!DOCTYPE html>
<html <?php
language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php
        esc_html_e('GiveWP &rsaquo; Onboarding Wizard', 'give'); ?></title>
    <?php
    wp_print_styles(['give-styles']); ?>

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .iframe-loader {
            min-height: 776px !important;
        }
    </style>
</head>
<body class="<?php
echo esc_attr($wp_version_class); ?>">
<?php
echo give_form_shortcode(
    [
        'id' => $this->get_preview_form_id(),
    ]
);
?>
<?php
wp_print_scripts(['give']); ?>
<script>
    (function checkForBodySizeChange() {
        var last_body_size = {
            width: document.body.clientWidth,
            height: document.body.clientHeight,
        };

        function checkBodySizeChange() {
            var width_changed = last_body_size.width !== document.body.clientWidth,
                height_changed = last_body_size.height !== document.body.clientHeight;


            if (width_changed || height_changed) {
                handleBodySizeChange(document.body.clientWidth, document.body.clientHeight);
                last_body_size = {
                    width: document.body.clientWidth,
                    height: document.body.clientHeight,
                };
            }

            window.requestAnimationFrame(checkBodySizeChange);
        }

        function handleBodySizeChange(width, height) {
            window.parent.postMessage({
                action: 'resize',
                payload: {
                    height,
                    width,
                },
            });
        }

        window.requestAnimationFrame(checkBodySizeChange);
    })();
</script>
<script>
    (function listenForFormLoaded() {
        function handleFormLoaded(width, height) {
            window.parent.postMessage({
                action: 'loaded',
                payload: {},
            });
        }

        document.querySelector('iframe').addEventListener('load', handleFormLoaded);
    })();
</script>
</body>
</html>
