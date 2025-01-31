<?php

namespace Give\Campaigns\Blocks\CampaignDonationsBlock;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 */
if ( ! class_exists(CampaignDonationsBlockRenderer::class)) {
    class CampaignDonationsBlockRenderer
    {
        /**
         * @var Campaign $campaign
         */
        private $campaign;

        /**
         * @var array $attributes
         */
        private $attributes;

        /**
         * @unreleased
         */
        public function __construct($attributes)
        {
            $this->attributes = $attributes;
            $this->campaign = $this->fetchCampaign();
        }

        /**
         * @unreleased
         */
        private function fetchCampaign(): ?Campaign
        {
            if ( ! isset($this->attributes['campaignId'])) {
                return null;
            }

            return give(CampaignRepository::class)->getById($this->attributes['campaignId']);
        }

        /**
         * @unreleased
         */
        public function render(): void
        {
            if ( ! $this->campaign) {
                return;
            }

            $query = $this->buildDonationsQuery();
            $donations = $this->formatDonationsData($query->getAll());
            $this->renderBlockHtml($donations);
        }

        /**
         * @unreleased
         */
        private function buildDonationsQuery(): CampaignDonationQuery
        {
            $sortBy = $this->attributes['sortBy'] ?? 'top-donations';
            $query = (new CampaignDonationQuery($this->campaign))
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
                ->limit($this->attributes['donationsPerPage'] ?? 5);

            if ( ! $this->attributes['showAnonymous']) {
                $query->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
                    ->where('anonymousMeta.meta_value', '0');
            }

            return $query;
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

        /**
         * @unreleased
         */
        private function renderBlockHtml($donations): void
        {
            $sortBy = $this->attributes['sortBy'] ?? 'top-donations';
            $blockTitle = $sortBy === 'top-donations' ? __('Top Donations', 'give') : __(
                'Recent Donations',
                'give'
            );
            $donateButtonText = $this->attributes['donateButtonText'] ?? __('Donate', 'give');

            $blockInlineStyles = sprintf(
                '--givewp-primary-color: %s; --givewp-secondary-color: %s;',
                esc_attr('#0b72d9'),
                esc_attr('#27ae60')
            );
            ?>
            <div
                <?php
                echo wp_kses_data(get_block_wrapper_attributes(['class' => 'givewp-campaign-donations-block'])); ?>
                style="<?php
                echo esc_attr($blockInlineStyles); ?>">
                <div class="givewp-campaign-donations-block__header">
                    <h2 class="givewp-campaign-donations-block__title"><?php
                        echo esc_html($blockTitle); ?></h2>
                    <?php
                    if ($this->attributes['showButton'] && ! empty($donations)) : ?>
                        <div class="givewp-campaign-donations-block__donate-button">
                            <?php
                            $params = [
                                'formId' => $this->campaign->defaultFormId,
                                'openFormButton' => $donateButtonText,
                                'formFormat' => 'modal',
                            ];

                            echo (new BlockRenderController())->render($params);
                            ?>
                        </div>
                    <?php
                    endif; ?>
                </div>

                <?php
                if (empty($donations)) : ?>
                    <div class="givewp-campaign-donations-block__empty-state">
                        <h3 class="givewp-campaign-donations-block__empty-title">
                            <?php
                            _e('Every campaign starts with one donation.', 'give'); ?>
                        </h3>
                        <p class="givewp-campaign-donations-block__empty-description">
                            <?php
                            _e('Be the one to mate it happen!', 'give'); ?>
                        </p>

                        <svg class="givewp-campaign-donations-block__empty-icon" width="40" height="40" viewBox="0 0 40 40"
                             fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 33.333c3.893-4.129 9.178-6.666 15-6.666 5.822 0 11.107 2.537 15 6.666M27.5 12.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0z"
                                stroke="currentColor" stroke-width="3.333" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        <?php
                        if ($this->attributes['showButton']) : ?>
                            <div class="givewp-campaign-donations-block__empty-button">
                                <?php
                                $params = [
                                    'formId' => $this->campaign->defaultFormId,
                                    'openFormButton' => __('Be the first', 'give'),
                                    'formFormat' => 'modal',
                                ];

                                echo (new BlockRenderController())->render($params);
                                ?>
                            </div>
                        <?php
                        endif; ?>
                    </div>
                <?php
                else : ?>
                    <ul class="givewp-campaign-donations-block__donations">
                        <?php
                        foreach ($donations as $key => $donation) : ?>
                            <li class="givewp-campaign-donations-block__donation">
                                <?php
                                if ($this->attributes['showIcon']) : ?>
                                    <div class="givewp-campaign-donations-block__donation-icon">
                                        <img
                                            src="<?php
                                            echo get_avatar_url($donation->donorId, ['size' => 64]); ?>"
                                            alt="<?php
                                            _e('Donation icon', 'give'); ?>"
                                        />
                                    </div>
                                <?php
                                endif; ?>

                                <div class="givewp-campaign-donations-block__donation-info">
                                    <div class="givewp-campaign-donations-block__donation-description">
                                        <?php
                                        printf(
                                            __('%s donated %s', 'give'),
                                            '<strong>' . esc_html($donation->donorName) . '</strong>',
                                            '<strong>' . esc_html($donation->amount->formatToLocale()) . '</strong>'
                                        );
                                        ?>
                                    </div>

                                    <span class="givewp-campaign-donations-block__donation-date"><?php
                                        echo esc_html(
                                            sprintf(
                                                _x('%s ago', 'human-readable time difference', 'give'),
                                                $donation->date
                                            )
                                        ); ?></span>
                                </div>

                                <?php
                                if ($sortBy === 'top-donations' && $key < 3) : ?>
                                    <div class="givewp-campaign-donations-block__donation-ribbon" data-position="<?php
                                    echo esc_attr($key + 1); ?>">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                      d="M6 .5a4 4 0 0 0-2.55 7.082l-.446 3.352a.5.5 0 0 0 .753.495L6 10.083l2.243 1.346a.5.5 0 0 0 .753-.495L8.55 7.581A4 4 0 0 0 6 .5zM4.382 8.16c.495.218 1.042.34 1.618.34.576 0 1.124-.122 1.619-.341l.249 1.879-1.405-.843-.014-.01a.958.958 0 0 0-.288-.126.75.75 0 0 0-.322 0 .958.958 0 0 0-.288.127l-.014.009-1.405.843.25-1.879z"
                                                      fill="currentColor" />
                                            </svg>
                                    </div>
                                <?php
                                endif; ?>
                            </li>
                        <?php
                        endforeach; ?>
                    </ul>
                <?php
                endif; ?>
            </div>
            <?php
        }
    }
}

(new CampaignDonationsBlockRenderer($attributes))->render();
