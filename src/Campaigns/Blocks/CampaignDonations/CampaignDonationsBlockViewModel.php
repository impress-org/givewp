<?php

namespace Give\Campaigns\Blocks\CampaignDonations;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\ValueObjects\Money;
use Give\Framework\Views\View;

/**
 * @since 4.0.0
 */
class CampaignDonationsBlockViewModel
{
    /**
     * @var Campaign $campaign
     */
    private $campaign;

    /**
     * @var array
     */
    private $donations;

    /**
     * @var array $attributes
     */
    private $attributes;

    /**
     * @since 4.0.0
     */
    public function __construct(Campaign $campaign, array $donations, array $attributes)
    {
        $this->attributes = $attributes;
        $this->campaign = $campaign;
        $this->donations = $donations;
    }

    /**
     * @since 4.0.0
     */
    public function render(): void
    {
        View::render('Campaigns/Blocks/CampaignDonations.render', [
            'campaign' => $this->campaign,
            'donations' => $this->formatDonationsData($this->donations),
            'attributes' => $this->attributes,
        ]);
    }


    /**
     * @since 4.14.0 add avatar URL to donations data
     * @since 4.0.0
     */
    private function formatDonationsData(array $donations): array
    {
        return array_map(static function ($entry) {
            $entry->date = human_time_diff(strtotime($entry->date));
            $entry->amount = Money::fromDecimal($entry->amount, give_get_currency());

            if ($entry->isAnonymous) {
                $entry->donorAvatarUrl = get_avatar_url(0, ['size' => 80]);
            } else {
                $entry->donorAvatarUrl = (int) $entry->donorAvatarId > 0
                    ? wp_get_attachment_image_url($entry->donorAvatarId, ['width' => '80', 'height' => '80'])
                    : get_avatar_url($entry->email, ['size' => 80]);
            }
            return $entry;
        }, $donations);
    }
}
