<?php

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 *
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (
    !isset($attributes['campaignId'])
    || !$campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

$query = (new CampaignDonationQuery($campaign))
    ->select(
        $attributes['statistic'] === 'top-donation'
            ? 'MAX(amountMeta.meta_value) as amount'
            : 'AVG(amountMeta.meta_value) as amount'
    )
    ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta');

$donationStat = $query->get();

$amount = Money::fromDecimal($donationStat->amount, give_get_currency());
$title = $attributes['statistic'] === 'top-donation' ? __('Top Donation', 'give') : __('Average Donation', 'give');
?>

<div class="givewp-campaign-stats-block">
    <span><?php echo $title ?></span>
    <strong><?php echo esc_html($amount->formatToLocale()) ?></strong>
</div>
