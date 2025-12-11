<?php

namespace Give\Campaigns\Blocks\CampaignDonors;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;

/**
 * @since 4.2.0 remove SQL casting
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

$sortBy = $attributes['sortBy'] ?? 'top-donors';
$query = (new CampaignDonationQuery($campaign))
    ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
    ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
    ->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered')
    ->joinDonationMeta(DonationMetaKeys::FIRST_NAME, 'donorName')
    ->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
    ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
    ->limit($attributes['donorsPerPage'] ?? 5);

if ($sortBy === 'top-donors') {
    $query->select(
        'donorIdMeta.meta_value as id',
        'SUM(amountMeta.meta_value - IFNULL(feeAmountRecovered.meta_value, 0)) AS amount',
        'MAX(donorName.meta_value) AS name',
        'anonymousMeta.meta_value as isAnonymous'
    )
        ->groupBy('donorIdMeta.meta_value')
        ->orderBy('amount', 'DESC');
} else {
    $query->joinDonationMeta(DonationMetaKeys::COMPANY, 'companyMeta')
        ->select(
            'donation.ID as donationID',
            'donorIdMeta.meta_value as id',
            'companyMeta.meta_value as company',
            'donation.post_date as date',
            'amountMeta.meta_value - IFNULL(feeAmountRecovered.meta_value, 0) as amount',
            'donorName.meta_value as name',
            'anonymousMeta.meta_value as isAnonymous'
        )
        ->orderBy('donation.ID', 'DESC');
}

if ( ! $attributes['showAnonymous']) {
    $query->where('anonymousMeta.meta_value', '1', '!=');
}

(new CampaignDonorsBlockViewModel($campaign, $query->getAll(), $attributes))->render();
