<?php
/** @var array[] $banners */ ?>
<div class="give-sale-banners-container" style="display: none;">

    <svg style="display: none" id="give-sale-banners-icons">
        <?php
        /* vuesax/linear/close-circle */ ?>
        <path id="give-sale-banners-dismiss-icon-path"
              d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10ZM9.17 14.83l5.66-5.66M14.83 14.83 9.17 9.17"
              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>

    <?php
    foreach ($banners as $banner): extract($banner);
        /**
         * @var string $id
         * @var string $iconURL
         * @var string $accessibleLabel
         * @var string $leadText
         * @var string $contentText
         * @var string $actionText
         * @var string $actionURL
         * @var string $startDate
         * @var string $endDate
         */
        ?>

        <aside aria-label="<?= $accessibleLabel ?>" id="<?= $dismissableElementId = "give-sale-banner-{$id}" ?>"
               class="give-sale-banner">
            <img class="give-sale-banner-icon" src="<?= $iconURL ?>" alt="Sale">
            <div class="give-sale-banner-content">
                <p>
                    <strong><?= $leadText ?></strong> <?= $contentText ?> <a href="<?= $actionURL ?>" rel="noopener"
                                                                             target="_blank"><?= $actionText ?></a>
                </p>
            </div>
            <button type="button" aria-label="<?= __('Dismiss', 'give') ?> <?= $accessibleLabel ?>"
                    aria-controls="<?= $dismissableElementId ?>" class="give-sale-banner-dismiss" data-id="<?= $id ?>">
                <svg viewBox="0 0 24 24" focusable="false">
                    <use href="#give-sale-banners-dismiss-icon-path" />
                </svg>
            </button>
        </aside>

    <?php
    endforeach; ?>

</div>
