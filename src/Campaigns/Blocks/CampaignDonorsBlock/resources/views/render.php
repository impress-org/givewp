<?php

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

/**
 * @var Campaign $campaign
 * @var array $attributes
 */

$sortBy = $attributes['sortBy'] ?? 'top-donors';
$blockTitle = $sortBy === 'top-donors' ? __('Top Donors', 'give') : __(
    'Recent Donors',
    'give'
);
$donateButtonText = $attributes['donateButtonText'] ?? __('Join the list', 'give');

$blockInlineStyles = sprintf(
    '--givewp-primary-color: %s; --givewp-secondary-color: %s;',
    esc_attr('#0b72d9'),
    esc_attr('#27ae60')
);
?>
<div
    <?php
    echo wp_kses_data(get_block_wrapper_attributes(['class' => 'givewp-campaign-donors-block'])); ?>
    style="<?php
    echo esc_attr($blockInlineStyles); ?>">
    <div class="givewp-campaign-donors-block__header">
        <h2 class="givewp-campaign-donors-block__title"><?php
            echo esc_html($blockTitle); ?></h2>
        <?php
        if ($attributes['showButton'] && ! empty($donors)) : ?>
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
        <?php
        endif; ?>
    </div>

    <?php
    if (empty($donors)) : ?>
        <div class="givewp-campaign-donors-block__empty-state">
            <h3 class="givewp-campaign-donors-block__empty-title">
                <?php
                $emptyStateTitle = $sortBy === 'top-donors'
                    ? __('No top donors listed yet.', 'give')
                    : __('No recent donors listed yet.', 'give');
                echo esc_html($emptyStateTitle);
                ?>
            </h3>
            <p class="givewp-campaign-donors-block__empty-description">
                <?php
                esc_html_e('Be one of the first to make an impact!', 'give'); ?>
            </p>

            <svg class="givewp-campaign-donors-block__empty-icon" width="40" height="40" viewBox="0 0 40 40"
                 fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5 33.333c3.893-4.129 9.178-6.666 15-6.666 5.822 0 11.107 2.537 15 6.666M27.5 12.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0z"
                    stroke="currentColor" stroke-width="3.333" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>

            <?php
            if ($attributes['showButton']) : ?>
                <div class="givewp-campaign-donors-block__empty-button">
                    <?php
                    $params = [
                        'formId' => $campaign->defaultFormId,
                        'openFormButton' => __('Be the first donor', 'give'),
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
        <ul class="givewp-campaign-donors-block__donors">
            <?php
            foreach ($donors as $key => $donor) : ?>
                <li class="givewp-campaign-donors-block__donor">
                    <?php
                    if ($attributes['showAvatar']) : ?>
                        <div class="givewp-campaign-donors-block__donor-avatar">
                            <img
                                src="<?php
                                echo get_avatar_url($donor->id, ['size' => 64]); ?>"
                                alt="<?php
                                _e('Donor avatar', 'give'); ?>"
                            />
                        </div>
                    <?php
                    endif; ?>

                    <div class="givewp-campaign-donors-block__donor-info">
                                    <span class="givewp-campaign-donors-block__donor-name"><?php
                                        echo esc_html($donor->name); ?></span>

                        <?php
                        if ($sortBy === 'top-donors' && $key < 3) : ?>
                            <span class="givewp-campaign-donors-block__donor-ribbon" data-position="<?php
                            echo esc_attr($key + 1); ?>">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                      d="M6 .5a4 4 0 0 0-2.55 7.082l-.446 3.352a.5.5 0 0 0 .753.495L6 10.083l2.243 1.346a.5.5 0 0 0 .753-.495L8.55 7.581A4 4 0 0 0 6 .5zM4.382 8.16c.495.218 1.042.34 1.618.34.576 0 1.124-.122 1.619-.341l.249 1.879-1.405-.843-.014-.01a.958.958 0 0 0-.288-.126.75.75 0 0 0-.322 0 .958.958 0 0 0-.288.127l-.014.009-1.405.843.25-1.879z"
                                                      fill="currentColor" />
                                            </svg>
                                        </span>
                        <?php
                        endif; ?>

                        <?php
                        if ($sortBy === 'recent-donors' && isset($donor->date)) : ?>
                            <span class="givewp-campaign-donors-block__donor-date"><?php
                                echo esc_html(
                                    sprintf(
                                        _x('%s ago', 'human-readable time difference', 'give'),
                                        $donor->date
                                    )
                                ); ?></span>
                        <?php
                        endif; ?>

                        <?php
                        if ($attributes['showCompanyName'] && isset($donor->company) && $donor->company) : ?>
                            <span class="givewp-campaign-donors-block__donor-company"><?php
                                echo esc_html($donor->company); ?></span>
                        <?php
                        endif; ?>
                    </div>
                    <div class="givewp-campaign-donors-block__donor-amount"><?php
                        echo esc_html($donor->amount->formatToLocale()); ?></div>
                </li>
            <?php
            endforeach; ?>
        </ul>
    <?php
    endif; ?>
</div>
