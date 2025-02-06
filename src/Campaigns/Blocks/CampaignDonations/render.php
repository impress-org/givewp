<?php

namespace Give\Campaigns\Blocks\CampaignDonations;

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

$sortBy = $attributes['sortBy'] ?? 'top-donations';
$query = (new CampaignDonationQuery($campaign))
    ->select(
        'donation.ID as id',
        'donorIdMeta.meta_value as donorId',
        'amountMeta.meta_value as amount',
        'donation.post_date as date',
        'donors.name as donorName'
    )
    ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
    ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
    ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
    ->orderBy($sortBy === 'top-donations' ? 'amount' : 'donation.ID', 'DESC')
    ->limit($attributes['donationsPerPage'] ?? 5);

if ( ! $attributes['showAnonymous']) {
    $query->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
        ->where('anonymousMeta.meta_value', '0');
}

(new CampaignDonationsBlockViewModel($campaign, $query->getAll(), $attributes))->render();
