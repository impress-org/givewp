<?php
/**
 * @since TBD Add translators comments.
 */

/**
 * @since TBD Escape output.
 */

if (!defined('ABSPATH')) {
    exit;
}

$pageId = give_get_option('donor_dashboard_page');

$pageUrl = get_permalink($pageId);

?>

<div class="notice notice-success is-dismissible">
    <p><?php
        echo wp_kses_post( sprintf(
            /* translators: %s: URL of the Donor Dashboard page */
            __('Success! Donor Dashboard page was created. You can <a href="%s">take a look at it here.</a>', 'give'),
            esc_url( $pageUrl )
        ) ); ?></p>
</div>
