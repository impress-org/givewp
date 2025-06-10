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
     * @since 4.0.0
     */
    private function formatDonorsData(array $donors): array
    {
        return array_map(static function ($entry) {
            if (isset($entry->date)) {
                $entry->date = human_time_diff(strtotime($entry->date));
            }
            $entry->amount = Money::fromDecimal($entry->amount, give_get_currency());

            return $entry;
        }, $donors);
    }
}
