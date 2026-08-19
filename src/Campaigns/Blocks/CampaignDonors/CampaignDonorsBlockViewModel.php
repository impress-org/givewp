<?php

namespace Give\Campaigns\Blocks\CampaignDonors;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\ValueObjects\Money;
use Give\Framework\Views\View;

/**
 * @since 4.0.0
 */
class CampaignDonorsBlockViewModel
{
    /**
     * @var Campaign $campaign
     */
    private $campaign;

    /**
     * @var array
     */
    private $donors;

    /**
     * @var array $attributes
     */
    private $attributes;

    /**
     * @since 4.0.0
     */
    public function __construct(Campaign $campaign, array $donors, array $attributes)
    {
        $this->attributes = $attributes;
        $this->campaign = $campaign;
        $this->donors = $donors;
    }

    /**
     * @since 4.0.0
     */
    public function render(): void
    {
        View::render('Campaigns/Blocks/CampaignDonors.render', [
            'campaign' => $this->campaign,
            'donors' => $this->formatDonorsData($this->donors),
            'attributes' => $this->attributes,
        ]);
    }


    /**
     * @since 4.14.0 add avatar URL to donors data
     * @since 4.0.0
     */
    private function formatDonorsData(array $donors): array
    {
        return array_map(static function ($entry) {
            if (isset($entry->date)) {
                $entry->date = human_time_diff(strtotime($entry->date));
            }
            $entry->amount = Money::fromDecimal($entry->amount, give_get_currency());
            if ($entry->isAnonymous) {
                $entry->avatarUrl = get_avatar_url(0, ['size' => 80]);
            } else {
                $entry->avatarUrl = (int) $entry->avatarId > 0
                    ? wp_get_attachment_image_url($entry->avatarId, ['width' => '80', 'height' => '80'])
                    : get_avatar_url($entry->email, ['size' => 80]);
            }

            return $entry;
        }, $donors);
    }
}
