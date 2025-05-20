<?php

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 4.0.0
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
        ? 'MAX(amountMeta.meta_value - IFNULL(feeAmountRecovered.meta_value, 0)) as amount'
        : 'AVG(amountMeta.meta_value - IFNULL(feeAmountRecovered.meta_value, 0)) as amount'
    )
    ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
    ->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered');

$donationStat = $query->get();

$amount = $donationStat && $donationStat->amount
    ? Money::fromDecimal($donationStat->amount, give_get_currency())
    : Money::fromDecimal(0, give_get_currency());

$title = $attributes['statistic'] === 'top-donation' ? __('Top Donation', 'give') : __('Average Donation', 'give');
?>

<div <?= get_block_wrapper_attributes(['class' => 'givewp-campaign-stats-block']) ?>>
    <span><?php echo $title ?></span>
    <strong><?php echo esc_html($amount->formatToLocale()) ?></strong>
</div>
