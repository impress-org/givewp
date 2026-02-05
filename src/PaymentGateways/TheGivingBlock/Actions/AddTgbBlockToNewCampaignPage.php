<?php

namespace Give\PaymentGateways\TheGivingBlock\Actions;

use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * Adds The Giving Block donation form block to the default campaign page layout when an organization is connected.
 * The block is inserted below the regular campaign donate button. Applied via givewp_campaign_page_default_layout filter.
 *
 * @unreleased
 */
class AddTgbBlockToNewCampaignPage
{
    private const OR_PARAGRAPH_MARKUP = '<!-- wp:paragraph -->
<p>OR</p>
<!-- /wp:paragraph -->';

    private const TGB_BLOCK_MARKUP = '<!-- wp:give/donation-form-block {"displayType":"popup","popupButtonText":"Donate Crypto/Stock"} /-->';

    /**
     * @unreleased
     *
     * @param string $content          Default layout block markup.
     * @param int    $campaignId      Campaign ID (unused).
     * @param string $shortDescription Campaign short description (unused).
     * @return string Filtered content.
     */
    public function __invoke(string $content, int $campaignId, string $shortDescription): string
    {
        if (!OrganizationRepository::isConnected()) {
            return $content;
        }

        if (strpos($content, 'give/donation-form-block') !== false) {
            return $content;
        }

        $donateButtonPattern = '/(<!-- wp:givewp\/campaign-donate-button \{[^}]+\} \/-->)/';

        if (!preg_match($donateButtonPattern, $content)) {
            return $content;
        }

        $insert = "\n\n" . self::OR_PARAGRAPH_MARKUP . "\n\n" . self::TGB_BLOCK_MARKUP;

        return (string) preg_replace(
            $donateButtonPattern,
            '$1' . $insert,
            $content,
            1
        );
    }
}
