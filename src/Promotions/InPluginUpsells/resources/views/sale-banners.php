<?php /**
 * @since TBD Escape output.
 * @var array[] $banners
 */?>
<div class="givewp-sale-banners-container" style="display: none;">
    <?php
    foreach ($banners as $banner): extract($banner);
        /**
         * @var string $id
         * @var string $giveIconURL
         * @var string $discountIconURL
         * @var string $backgroundImageLargeURL
         * @var string $backgroundImageMediumURL
         * @var string $backgroundImageSmallURL
         * @var string $shoppingCartIconURL
         * @var string $dismissIconURL
         * @var string $accessibleLabel
         * @var string $leadText
         * @var string $actionText
         * @var string $actionURL
         * @var string $startDate
         * @var string $endDate
         */

        $discount_percentage = 40;
        $header = sprintf(
            __('Save %s on GiveWP Today.', 'give'),
            '<strong>' . $discount_percentage . '%</strong>'
        );
        ?>

        <aside aria-label="<?= esc_attr($accessibleLabel) ?>" id="<?= esc_attr($dismissableElementId = "givewp-sale-banner-{$id}") ?>"
               class="givewp-sale-banner">
            <div class="givewp-sale-banner__content">
                <h2> <?php echo wp_kses_post($header) ?> </h2>

                <p> <?php echo wp_kses_post($leadText) ?> </p>

                <a href="<?php echo esc_url($actionURL) ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo esc_url($shoppingCartIconURL) ?>" alt="cart"/>

                    <?php echo esc_html__('Shop now', 'give') ?>
                </a>
            </div>

            <button type="button" class="givewp-sale-banner__dismiss" aria-label="<?= esc_attr__('Dismiss', 'give') ?> <?= esc_attr($accessibleLabel) ?>">
                <img aria-controls="<?= esc_attr($dismissableElementId) ?>" data-id="<?= esc_attr($id) ?>" src="<?php echo esc_url($dismissIconURL) ?>" alt="dismiss"/>
            </button>
        </aside>

        <style>
            /* Default background image for Admin pages */
            .givewp-sale-banners-container {
                background-image: url('<?= esc_url($backgroundImageLargeURL) ?>');
            }

            /* Default background image Addons page */
            #give-in-plugin-upsells .givewp-sale-banners-container {
                background-image: url('<?= esc_url($backgroundImageMediumURL) ?>');
            }

            /* Media query for small screens */
            @media screen and (max-width: 768px) {
                .givewp-sale-banners-container {
                    background-image: url('<?= esc_url($backgroundImageSmallURL) ?>')!important;
                }
            }

            /* Media query for medium screens */
            @media screen and (min-width: 769px) and (max-width: 1278px) {
               .givewp-sale-banners-container {
                    background-image: url('<?= esc_url($backgroundImageMediumURL) ?>');
                }
            }
        </style>
    <?php
    endforeach; ?>
</div>
