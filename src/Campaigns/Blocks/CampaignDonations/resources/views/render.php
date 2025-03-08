<?php

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

/**
 * @var Campaign $campaign
 * @var array $attributes
 */

$sortBy = $attributes['sortBy'] ?? 'top-donations';
$blockTitle = $sortBy === 'top-donations' ? __('Top Donations', 'give') : __('Recent Donations', 'give');
$donateButtonText = $attributes['donateButtonText'] ?? __('Donate', 'give');

$blockInlineStyles = sprintf(
    '--givewp-primary-color: %s; --givewp-secondary-color: %s;',
    esc_attr($campaign->primaryColor ?? '#0b72d9'),
    esc_attr($campaign->secondaryColor ?? '#27ae60')
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
        if ($attributes['showButton'] && ! empty($donations)) : ?>
            <div class="givewp-campaign-donations-block__donate-button">
                <?php
                echo (new BlockRenderController())->render([
                    'formId' => $campaign->defaultFormId,
                    'openFormButton' => $attributes['donateButtonText'],
                    'formFormat' => 'modal',
                ]); ?>
            </div>
        <?php
        endif; ?>
    </div>

    <?php
    if (empty($donations)) : ?>
        <div class="givewp-campaign-donations-block__empty-state">
            <h3 class="givewp-campaign-donations-block__empty-title">
                <?php
                esc_html_e('Every campaign starts with one donation.', 'give'); ?>
            </h3>
            <p class="givewp-campaign-donations-block__empty-description">
                <?php
                esc_html_e('Be the one to mate it happen!', 'give'); ?>
            </p>

            <div class="givewp-campaign-donations-block__empty-icon">
                <?php
                echo file_get_contents(dirname(__DIR__) . '/icons/empty-state.svg'); ?>
            </div>

            <?php
            if ($attributes['showButton']) : ?>
                <div class="givewp-campaign-donations-block__empty-button">
                    <?php
                    echo (new BlockRenderController())->render([
                        'formId' => $campaign->defaultFormId,
                        'openFormButton' => __('Be the first', 'give'),
                        'formFormat' => 'modal',
                    ]);
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
                    if ($attributes['showIcon']) : ?>
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
                            <?php
                            echo file_get_contents(dirname(__DIR__) . '/icons/ribbon.svg'); ?>
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
