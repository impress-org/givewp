<?php
/** @var array[] $banners */ ?>
<div class="give-sale-banners-container" style="display: none;">
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

        <aside aria-label="<?= $accessibleLabel ?>" id="<?= $dismissableElementId = "give-sale-banner-{$id}" ?>"
               class="givewp-sale-banner">
            <div class="givewp-sale-banner__content">
                <h2> <?php echo $header ?> </h2>

                <p> <?php echo $leadText ?> </p>

                <a href="<?php echo $actionURL ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo $shoppingCartIconURL ?>" alt="cart"/>

                    <?php echo __('Shop now', 'give') ?>
                </a>
            </div>

            <button type="button" aria-label="<?= __('Dismiss', 'give') ?> <?= $accessibleLabel ?>"
                    aria-controls="<?= $dismissableElementId ?>" class="givewp-sale-banner__dismiss"
                    data-id="<?= $id ?>">

                <img src="<?php echo $dismissIconURL ?>" alt="dismiss"/>
            </button>
        </aside>

        <style>
            /* Default background image */
            .give-sale-banners-container {
                background-image: url('<?= $backgroundImageLargeURL ?>');
            }

            /* Media query for small screens */
            @media screen and (max-width: 768px) {
                .give-sale-banners-container {
                    background-image: url('<?= $backgroundImageSmallURL ?>');
                }
            }

            /* Media query for medium screens */
            @media screen and (min-width: 769px) and (max-width: 1024px) {
                .give-sale-banners-container {
                    background-image: url('<?= $backgroundImageMediumURL ?>');
                }
            }
        </style>
    <?php
    endforeach; ?>
</div>
