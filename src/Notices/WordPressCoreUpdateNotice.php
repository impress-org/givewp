<?php

declare(strict_types=1);

namespace Give\Notices;

use Give\Vendors\StellarWP\AdminNotices\AdminNotices;

/**
 * Prompts site administrators to update WordPress while the install is behind the latest release.
 *
 * @since TBD
 */
class WordPressCoreUpdateNotice
{
    /**
     * Dismissal flag shared with the other StellarWP plugins that display this notice, so a site
     * running more than one of them only has to dismiss it once.
     *
     * @since TBD
     */
    const DISMISSED_OPTION = 'nx_wp_core_update_notice_dismissed';

    /**
     * @since TBD
     */
    const NOTICE_ID = 'givewp-wp-core-update';

    /**
     * @since TBD
     */
    const DISMISS_ACTION = 'givewp-dismiss-wp-core-update-notice';

    /**
     * Registers the notice with the admin notices library.
     *
     * @since TBD
     */
    public function registerNotice(): void
    {
        AdminNotices::show(self::NOTICE_ID, [$this, 'renderNotice'])
            ->asWarning()
            ->dismissible()
            ->withoutAutoParagraph()
            ->ifUserCan('update_core')
            ->when([$this, 'shouldDisplay']);
    }

    /**
     * Stores the shared dismissal flag when the notice's dismiss control is used.
     *
     * @since TBD
     */
    public function handleDismissal(): void
    {
        if (!isset($_GET[self::DISMISS_ACTION])) {
            return;
        }

        check_admin_referer(self::DISMISS_ACTION);

        if (!current_user_can('update_core')) {
            return;
        }

        update_option(self::DISMISSED_OPTION, true, false);

        wp_safe_redirect(remove_query_arg([self::DISMISS_ACTION, '_wpnonce']));
        exit;
    }

    /**
     * @since TBD
     *
     * @return bool True while a core update is available and the notice has not been dismissed.
     */
    public function shouldDisplay(): bool
    {
        return !$this->isDismissed() && $this->isCoreUpdateAvailable();
    }

    /**
     * @since TBD
     *
     * @return string The notice contents.
     */
    public function renderNotice(): string
    {
        $heading = esc_html__('Keep your site protected. Update to the latest version of WordPress.', 'give');

        $body = esc_html__(
            'Your site is running on an outdated version of WordPress, which can leave it vulnerable to security issues. To decrease your risk of exposure, please update your WordPress install to the latest version.',
            'give'
        );

        /* The dismiss control is a link so the shared flag can be stored server side, without a script. */
        $dismiss = sprintf(
            '<a href="%1$s" class="notice-dismiss" style="text-decoration: none; color: #1e1e1e;">'
            . '<span class="screen-reader-text">%2$s</span></a>',
            esc_url($this->getDismissUrl()),
            esc_html__('Dismiss this notice.', 'give')
        );

        return "<p><strong>$heading</strong></p><p>$body</p>$dismiss";
    }

    /**
     * @since TBD
     */
    private function isDismissed(): bool
    {
        return (bool) get_option(self::DISMISSED_OPTION, false);
    }

    /**
     * @since TBD
     */
    private function isCoreUpdateAvailable(): bool
    {
        if (!function_exists('get_core_updates')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        $updates = get_core_updates(['dismissed' => false]);

        if (empty($updates) || !isset($updates[0]->response)) {
            return false;
        }

        return $updates[0]->response === 'upgrade';
    }

    /**
     * @since TBD
     */
    private function getDismissUrl(): string
    {
        return wp_nonce_url(add_query_arg(self::DISMISS_ACTION, '1'), self::DISMISS_ACTION);
    }
}
