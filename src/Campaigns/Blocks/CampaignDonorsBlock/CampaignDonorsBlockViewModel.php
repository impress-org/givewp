<?php

namespace Give\Campaigns\Blocks\CampaignDonorsBlock;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\ValueObjects\Money;
use Give\Framework\Views\View;

/**
 * @unreleased
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
     * @unreleased
     */
    public function __construct(Campaign $campaign, array $donors, array $attributes)
    {
        $this->attributes = $attributes;
        $this->campaign = $campaign;
        $this->donors = $donors;
    }

    /**
     * @unreleased
     */
    public function render(): void
    {
        View::render('Campaigns/Blocks/CampaignDonorsBlock.render', [
            'campaign' => $this->campaign,
            'donors' => $this->formatDonorsData($this->donors),
            'attributes' => $this->attributes,
        ]);
    }


    /**
     * @unreleased
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
