<?php
/**
 * @unrleased
 */

/** @var array[] $banner */

extract($banner[0])
/**
 * @var string $id
 * @var string $iconURL
 * @var string $accessibleLabel
 * @var string $leadHeader
 * @var string $leadText
 * @var string $contentText
 * @var string $actionText
 * @var string $alternateActionText
 * @var string $actionURL
 * @var string $alternateActionURL
 * @var string $startDate
 * @var string $endDate
 */
?>
<div class="give-sale-banners-container" style="display: none;">
    <aside aria-label="<?= $accessibleLabel ?>" id="<?= $dismissableElementId = "give-sale-banner-{$id}" ?>"
           class="give-sale-banner">

        <div class="give-sale-banner-content">
            <div class="give-sale-banner-content__primary-cta">
                <h1><?= $leadHeader ?></h1>
                <p><?= $leadText ?></p>

                <a class="give-sale-banner-content__primary-cta-link" href="<?= $actionURL ?>" rel="noopener"
                   target="_blank"><?= $actionText ?></a>

                <a class="give-sale-banner-content__primary-cta-mobile-link" href="<?= $alternateActionURL ?>"
                   rel="noopener"
                   target="_blank"><?= $alternateActionText ?></a>
            </div>

            <div class="give-sale-banner-content__secondary-cta">
                <p><?= $contentText ?></p>
                <a href="<?= $alternateActionURL ?>" rel="noopener"
                   target="_blank"><?= $alternateActionText ?></a>
            </div>
        </div>

        <div class="give-sale-banner__abstract-icon">
            <svg width="280" height="154" viewBox="0 0 280 154" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_1482_402)">
                    <circle cx="10.2895" cy="9.58348" r="9.58348" fill="#F9FAF9" />
                    <circle cx="245.085" cy="46.9587" r="4.79174" fill="#F9FAF9" />
                    <path
                        d="M245.085 46.0009L103.249 220.42L9.33105 7.66699L245.085 46.0009ZM245.085 46.0009L306.419 124.586"
                        stroke="#F9FAF9" stroke-width="1.43752" />
                </g>
                <defs>
                    <clipPath id="clip0_1482_402">
                        <rect width="280" height="154" fill="white" />
                    </clipPath>
                </defs>
            </svg>
        </div>
        <button type="button" aria-label="<?= __('Dismiss', 'give') ?> <?= $accessibleLabel ?>"
                aria-controls="<?= $dismissableElementId ?>" class="give-sale-banner-dismiss"
                data-id="<?= $id ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19" viewBox="0 0 20 19" fill="none">
                <line x1="1.35355" y1="0.646447" x2="19.3535" y2="18.6464" stroke="#F9FAF9" />
                <line y1="-0.5" x2="25.4558" y2="-0.5"
                      transform="matrix(0.707107 -0.707106 0.707107 0.707106 1 19)"
                      stroke="#F9FAF9" />
            </svg>
        </button>
    </aside>
    <br>
</div>
