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

            <svg class="givewp-campaign-donations-block__empty-icon" width="40" height="40" viewBox="0 0 40 40"
                 fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M11.6628 31.6719H28.3294M31.6628 26.6719C32.9578 26.7102 33.7294 26.8552 34.2628 27.3902C34.9961 28.1235 34.9961 29.3035 34.9961 31.6652C34.9961 34.0252 34.9961 35.2052 34.2628 35.9385C33.5311 36.6719 32.3528 36.6719 29.9961 36.6719H9.99609C7.63943 36.6719 6.46109 36.6719 5.72943 35.9385C4.99776 35.2052 4.99609 34.0252 4.99609 31.6652C4.99609 29.3052 4.99609 28.1235 5.72943 27.3902C6.26276 26.8569 7.03443 26.7102 8.32943 26.6719"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path
                    d="M23.8559 31.6691C24.4744 31.1394 24.9712 30.4822 25.3121 29.7426C25.653 29.003 25.8299 28.1984 25.8309 27.3841C25.8309 24.2274 23.2192 21.6691 19.9975 21.6691C16.7759 21.6691 14.1642 24.2274 14.1642 27.3841C14.1651 28.1984 14.3421 29.003 14.683 29.7426C15.0239 30.4822 15.5206 31.1394 16.1392 31.6691M33.3309 16.6691H29.3392C28.8492 16.6691 28.3659 16.5591 27.9275 16.3457L24.5242 14.6991C24.0833 14.4868 23.6002 14.3769 23.1109 14.3774H21.3742C19.6942 14.3774 18.3309 13.0591 18.3309 11.4324C18.3309 11.3657 18.3759 11.3091 18.4409 11.2907L22.6759 10.1207C23.4356 9.91029 24.2459 9.98314 24.9559 10.3257L28.5942 12.0857M18.3309 12.5024L10.6759 14.8541C10.0094 15.0586 9.29545 15.0473 8.6358 14.8219C7.97614 14.5966 7.40453 14.1686 7.00254 13.5991C6.38754 12.7491 6.63754 11.5291 7.5342 11.0124L20.0592 3.78407C20.4508 3.55747 20.8845 3.41297 21.3338 3.35939C21.7831 3.30581 22.2386 3.34426 22.6725 3.4724L33.3309 6.63573"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>

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
