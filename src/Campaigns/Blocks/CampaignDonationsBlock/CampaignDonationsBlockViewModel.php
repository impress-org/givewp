<?php

namespace Give\Campaigns\Blocks\CampaignDonationsBlock;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\ValueObjects\Money;
use Give\Framework\Views\View;

/**
 * @unreleased
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
     * @unreleased
     */
    public function __construct(Campaign $campaign, array $donations, array $attributes)
    {
        $this->attributes = $attributes;
        $this->campaign = $campaign;
        $this->donations = $donations;
    }

    /**
     * @unreleased
     */
    public function render(): void
    {
        View::render('Campaigns/Blocks/CampaignDonationsBlock.render', [
            'campaign' => $this->campaign,
            'donations' => $this->formatDonationsData($this->donations),
            'attributes' => $this->attributes,
        ]);
    }


    /**
     * @unreleased
     */
    private function formatDonationsData(array $donations): array
    {
        return array_map(static function ($entry) {
            $entry->date = human_time_diff(strtotime($entry->date));
            $entry->amount = Money::fromDecimal($entry->amount, give_get_currency());

            return $entry;
        }, $donations);
    }
}
