<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Actions\CreateDefaultLayoutForCampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;

/**
 * Restore Gutenberg content when switching back from Elementor editor
 *
 * This class ensures that when users switch from Elementor back to the WordPress
 * editor for campaign pages, they see the original Gutenberg block content
 * rather than empty content.
 *
 * @since 4.0.0
 */
class RestoreGutenbergContentOnElementorExit
{
    /**
     * Hook into post save to detect Elementor mode changes
     *
     * @since 4.0.0
     * @param int $postId
     */
    public function __invoke(int $postId): void
    {
        // Only proceed if Elementor is active
        if (!$this->isElementorActive()) {
            return;
        }

        // Only proceed if we're switching FROM Elementor TO WordPress editor
        if (!$this->isSwitchingFromElementorToWordPress($postId)) {
            return;
        }

        // Only proceed if this is a campaign page
        if (!$this->isCampaignPage($postId)) {
            return;
        }

        $this->restoreGutenbergContent($postId);
    }

    /**
     * Check if this is a campaign page
     *
     * @since 4.0.0
     * @param int $postId
     * @return bool
     */
    private function isCampaignPage(int $postId): bool
    {
        $campaignId = get_post_meta($postId, CampaignPageMetaKeys::CAMPAIGN_ID, true);
        return !empty($campaignId);
    }

    /**
     * Check if we're switching from Elementor to WordPress editor
     *
     * @since 4.0.0
     * @param int $postId
     * @return bool
     */
    private function isSwitchingFromElementorToWordPress(int $postId): bool
    {
        // Check if this request is coming from Elementor's switch mode
        $elementorPostMode = isset($_POST['_elementor_post_mode']) ? $_POST['_elementor_post_mode'] : null;
        $nonce = isset($_POST['_elementor_edit_mode_nonce']) ? $_POST['_elementor_edit_mode_nonce'] : null;

        // Verify nonce if present (Elementor's nonce verification)
        if ($nonce && !wp_verify_nonce($nonce, basename(plugin_dir_path(__FILE__) . '../../../core/admin/admin.php'))) {
            return false;
        }

        // Check if we're switching away from Elementor (empty value means switching to WordPress editor)
        $wasBuiltWithElementor = $this->wasBuiltWithElementor($postId);
        $willBeBuiltWithElementor = !empty($elementorPostMode);

        return $wasBuiltWithElementor && !$willBeBuiltWithElementor;
    }

    /**
     * Check if post was previously built with Elementor
     *
     * @since 4.0.0
     * @param int $postId
     * @return bool
     */
    private function wasBuiltWithElementor(int $postId): bool
    {
        return get_post_meta($postId, '_elementor_edit_mode', true) === 'builder';
    }

    /**
     * Check if Elementor is active
     *
     * @since 4.0.0
     * @return bool
     */
    private function isElementorActive(): bool
    {
        return class_exists('\Elementor\Plugin') && defined('ELEMENTOR_VERSION');
    }

    /**
     * Restore the original Gutenberg content for the campaign page
     *
     * @since 4.0.0
     * @param int $postId
     */
    private function restoreGutenbergContent(int $postId): void
    {
        $campaignId = get_post_meta($postId, CampaignPageMetaKeys::CAMPAIGN_ID, true);

        if (!$campaignId) {
            return;
        }

        // Get the campaign page model
        $campaignPage = CampaignPage::find($postId);
        if (!$campaignPage) {
            return;
        }

        $campaign = $campaignPage->campaign();
        if (!$campaign) {
            return;
        }

        // Generate the original Gutenberg content
        $defaultLayoutAction = new CreateDefaultLayoutForCampaignPage();
        $gutenbergContent = $defaultLayoutAction($campaignId, $campaign->shortDescription);

        // Update the post content with Gutenberg blocks
        $postData = [
            'ID' => $postId,
            'post_content' => $gutenbergContent
        ];

        // Remove our own hook temporarily to avoid infinite loops
        remove_action('save_post', [$this, '__invoke']);

        wp_update_post($postData);

        // Re-add the hook
        add_action('save_post', [$this, '__invoke']);

        // Clean up Elementor meta data since we're switching away
        $this->cleanupElementorMeta($postId);
    }

    /**
     * Clean up Elementor-specific meta data when switching back to WordPress editor
     *
     * @since 4.0.0
     * @param int $postId
     */
    private function cleanupElementorMeta(int $postId): void
    {
        // Don't delete _elementor_data completely, just clear it
        // This allows users to switch back to Elementor later if needed
        update_post_meta($postId, '_elementor_data', '[]');

        // Remove our custom tracking meta
        delete_post_meta($postId, '_givewp_elementor_auto_template');
        delete_post_meta($postId, '_givewp_elementor_template_version');

        // Note: We don't remove _elementor_edit_mode here because
        // Elementor's own save_post handler will handle that
    }
}
