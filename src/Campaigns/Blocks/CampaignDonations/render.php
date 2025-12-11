<?php

namespace Give\Campaigns\Blocks\CampaignDonations;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;

/**
 * @since 4.2.0 remove SQL casting to decimal
 * @since 4.0.0
 *
 * @var array $attributes
 */

if ( ! isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if ( ! $campaign) {
    return;
}

$sortBy = $attributes['sortBy'] ?? 'top-donations';
$query = (new CampaignDonationQuery($campaign))
    ->select(
        'donation.ID as id',
        'donorIdMeta.meta_value as donorId',
        'amountMeta.meta_value - IFNULL(feeAmountRecovered.meta_value, 0) as amount',
        'donorName.meta_value as donorName',
        'donation.post_date as date',
        'anonymousMeta.meta_value as isAnonymous'
    )
    ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
    ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
    ->joinDonationMeta(DonationMetaKeys::FIRST_NAME, 'donorName')
    ->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered')
    ->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
    ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
    ->orderByRaw($sortBy === 'top-donations' ? 'amountMeta.meta_value DESC' : 'donation.ID DESC')
    ->limit($attributes['donationsPerPage'] ?? 5);

if ( ! $attributes['showAnonymous']) {
    $query->where('anonymousMeta.meta_value', '1', '!=');
}

(new CampaignDonationsBlockViewModel($campaign, $query->getAll(), $attributes))->render();
