<?php /** @var array[] $banners */?>
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

        <aside aria-label="<?= $accessibleLabel ?>" id="<?= $dismissableElementId = "givewp-sale-banner-{$id}" ?>"
               class="givewp-sale-banner">
            <div class="givewp-sale-banner__content">
                <h2> <?php echo $header ?> </h2>

                <p> <?php echo  $leadText ?> </p>

                <a href="<?php echo $actionURL ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo $shoppingCartIconURL ?>" alt="cart"/>

                    <?php echo __('Shop now', 'give') ?>
                </a>
            </div>

            <button type="button" class="givewp-sale-banner__dismiss" aria-label="<?= __('Dismiss', 'give') ?> <?= $accessibleLabel ?>">
                <img aria-controls="<?= $dismissableElementId ?>" data-id="<?= $id ?>" src="<?php echo $dismissIconURL ?>" alt="dismiss"/>
            </button>
        </aside>

        <style>
            /* Default background image for Admin pages */
            .givewp-sale-banners-container {
                background-image: url('<?= $backgroundImageLargeURL ?>');
            }

            /* Default background image Addons page */
            #give-in-plugin-upsells .givewp-sale-banners-container {
                background-image: url('<?= $backgroundImageMediumURL ?>');
            }

            /* Media query for small screens */
            @media screen and (max-width: 768px) {
                .givewp-sale-banners-container {
                    background-image: url('<?= $backgroundImageSmallURL ?>')!important;
                }
            }

            /* Media query for medium screens */
            @media screen and (min-width: 769px) and (max-width: 1278px) {
               .givewp-sale-banners-container {
                    background-image: url('<?= $backgroundImageMediumURL ?>');
                }
            }
        </style>
    <?php
    endforeach; ?>
</div>
