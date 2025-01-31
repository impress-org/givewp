<?php

namespace Give\Campaigns\Blocks\CampaignDonorsBlock;

use DateTime;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;

/**
 * @unreleased
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
    ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
    ->limit($attributes['donorsPerPage'] ?? 5);

if ($sortBy === 'top-donors') {
    $query->select(
        'donorIdMeta.meta_value as id',
        'SUM(amountMeta.meta_value) as amount',
        'donors.name as name'
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
            'amountMeta.meta_value as amount',
            'donors.name as name'
        )
        ->orderBy('donation.ID', 'DESC');
}

if ( ! $attributes['showAnonymous']) {
    $query->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
        ->where('anonymousMeta.meta_value', '0');
}

(new CampaignDonorsBlockViewModel($campaign, $query->getAll(), $attributes))->render();
