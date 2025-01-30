<?php

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Support\ValueObjects\Money;

if (! isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if (! $campaign) {
    return;
}

$sortBy = $attributes['sortBy'] ?? 'top-donors';

if ($sortBy === 'top-donors') {
    $query = (new CampaignDonationQuery($campaign))
        ->select(
            'donorIdMeta.meta_value as id',
            'SUM(amountMeta.meta_value) as amount',
            'donors.name as name'
        )
        ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
        ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
        ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
        ->limit($attributes['donorsPerPage'] ?? 5)
        ->groupBy('donorIdMeta.meta_value')
        ->orderBy('amount', 'DESC');
} else {
    $query = (new CampaignDonationQuery($campaign))
        ->select(
            'donation.ID as donationID',
            'donorIdMeta.meta_value as id',
            'companyMeta.meta_value as company',
            'donation.post_date as date',
            'amountMeta.meta_value as amount',
            'donors.name as name'
        )
        ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
        ->joinDonationMeta(DonationMetaKeys::COMPANY, 'companyMeta')
        ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amountMeta')
        ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors')
        ->limit($attributes['donorsPerPage'] ?? 5)
        ->orderBy('donation.ID', 'DESC');
}

if (!$attributes['showAnonymous']) {
    $query->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta');
    $query->where('anonymousMeta.meta_value', '0');
}

$donors = array_map(static function ($entry) {
    if (isset($entry->date)) {
        $entry->date = new DateTime($entry->date);
    }
    $entry->amount = Money::fromDecimal($entry->amount, give_get_currency());

    return $entry;
}, $query->getAll());

$blockTitle = $attributes['sortBy'] === 'top-donors' ? __('Top Donors', 'give') : __('Recent Donors', 'give');
$donateButtonText = $attributes['donateButtonText'] ?? __('Join the list', 'give');
$loadMoreButtonText = $attributes['loadMoreButtonText'] ?? __('Load more', 'give');

// TODO: Update these styles once Campaign Theme Settings are implemented
$blockInlineStyles = [
    '--givewp-campaign-donors-block__primary-color: ' . esc_attr('#0b72d9'),
    '--givewp-campaign-donors-block__secondary-color: ' . esc_attr('#27ae60'),
];

?>

<div
    <?php echo wp_kses_data(get_block_wrapper_attributes(['class' => 'givewp-campaign-donors-block'])); ?>
    style="<?php echo esc_attr(implode(';', $blockInlineStyles)); ?>"
>
    <div class="givewp-campaign-donors-block__header">
        <h2 class="givewp-campaign-donors-block__title"><?php echo esc_html($blockTitle); ?></h2>
        <?php if ($attributes['showButton'] && !empty($donors)) : ?>
            <div class="givewp-campaign-donors-block__donate-button">
                <?php
                $params = [
                    'formId' => $campaign->defaultFormId,
                    'openFormButton' => $donateButtonText,
                    'formFormat' => 'modal',
                ];

                echo (new BlockRenderController())->render($params);
                ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($donors)) : ?>
        <div class="givewp-campaign-donors-block__empty-state">
            <h3 class="givewp-campaign-donors-block__empty-title">
                <?php _e('No top donors listed yet.', 'give'); ?>
            </h3>
            <p class="givewp-campaign-donors-block__empty-description">
                <?php _e('Be one of the first to make an impact!', 'give'); ?>
            </p>

            <svg class="givewp-campaign-donors-block__empty-icon" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 33.333c3.893-4.129 9.178-6.666 15-6.666 5.822 0 11.107 2.537 15 6.666M27.5 12.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0z" stroke="currentColor" stroke-width="3.333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <?php if ($attributes['showButton']) : ?>
                <div class="givewp-campaign-donors-block__empty-button">
                    <?php
                    $params = [
                        'formId' => $campaign->defaultFormId,
                        'openFormButton' =>  __('Be the first donor', 'give'),
                        'formFormat' => 'modal',
                    ];

                    echo (new BlockRenderController())->render($params);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <ul class="givewp-campaign-donors-block__donors">
            <?php foreach ($donors as $key => $donor) : ?>
                <li class="givewp-campaign-donors-block__donor">
                    <?php if ($attributes['showAvatar']) : ?>
                        <div class="givewp-campaign-donors-block__donor-avatar">
                            <img
                                src="<?php echo get_avatar_url($donor->id, ['size' => 64]); ?>"
                                alt="<?php _e('Donor avatar', 'give'); ?>"
                            />
                        </div>
                    <?php endif; ?>

                    <div class="givewp-campaign-donors-block__donor-info">
                        <span class="givewp-campaign-donors-block__donor-name"><?php echo esc_html($donor->name); ?></span>

                        <?php if ($sortBy === 'top-donors' && $key < 3) : ?>
                            <span class="givewp-campaign-donors-block__donor-ribbon" data-position="<?php echo esc_attr($key + 1); ?>"></span>
                        <?php endif; ?>

                        <?php if ($sortBy === 'recent-donors') : ?>
                            <span class="givewp-campaign-donors-block__donor-date"><?php echo esc_html($donor->date->format('m/d/Y')); ?></span>
                        <?php endif; ?>

                        <?php
                        if ($attributes['showCompanyName'] && isset($donor->company) && $donor->company) : ?>
                            <span class="givewp-campaign-donors-block__donor-company"><?php echo esc_html($donor->company); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="givewp-campaign-donors-block__donor-amount"><?php echo esc_html($donor->amount->formatToLocale()); ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
