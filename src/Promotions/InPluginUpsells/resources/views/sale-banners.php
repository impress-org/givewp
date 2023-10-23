<?php
/** @var array[] $banners */ ?>
<div class="give-sale-banners-container" style="display: none;">

    <svg style="display: none" id="give-sale-banners-icons">
        <?php
        /* vuesax/linear/close-circle */ ?>
        <path id="give-sale-banners-dismiss-icon-path"
              d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10ZM9.17 14.83l5.66-5.66M14.83 14.83 9.17 9.17"
              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>

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
