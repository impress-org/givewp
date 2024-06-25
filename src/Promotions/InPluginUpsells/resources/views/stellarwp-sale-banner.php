<?php
/**
 * @since 3.13.0
 */

/** @var array[] $banners */
 foreach ($banners as $banner):
    $id = $banner['id'];
    $mainHeader = $banner['mainHeader'];
    $subHeader = $banner['subHeader'];
    $actionText = $banner['actionText'];
    $actionURL = $banner['actionURL'];
    $content = $banner['content'];
    $secondaryActionText = $banner['secondaryActionText'];
    $secondaryActionURL = $banner['secondaryActionURL'];
    $startDate = $banner['startDate'];
    $endDate = $banner['endDate'];
    ?>

    <div class="givewp-sale-banners-container">
        <aside aria-label="" id="<?php echo $dismissableElementId = "give-sale-banner-{$id}" ?>"
               class="give-sale-banner">
            <div class="give-sale-banner-content">
                <div class="give-sale-banner-content__primary-cta">
                    <h1 class="give-sale-banner-content__primary-cta__header"><?php echo $mainHeader ?></h1>
                    <h3 class="give-sale-banner-content__primary-cta__sub-header"><?php echo $subHeader ?></h3>
                    <a class="give-sale-banner-content__primary-cta__link" href="<?php echo $actionURL ?>"
                       rel="noopener" target="_blank"><?php echo $actionText ?></a>
                </div>

                <div class="give-sale-banner-content__secondary-cta">
                    <p class="give-sale-banner-content__secondary-cta__content"><?php echo wp_kses($content, ['strong' =>[]]) ?></p>
                    <a class="give-sale-banner-content__secondary-cta__link" href="<?php echo $secondaryActionURL ?>"
                       rel="noopener" target="_blank"><?php echo $secondaryActionText ?></a>
                </div>
            </div>

            <div class="give-sale-banner__abstract-icon">
                <svg width="230" height="124" viewBox="0 0 280 154" fill="none" xmlns="http://www.w3.org/2000/svg">
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
            <button type="button" aria-label="<?php echo __('Dismiss', 'give') ?>"
                    aria-controls="<?php echo $dismissableElementId ?>"
                    class="give-sale-banner-dismiss givewp-sale-banner__dismiss" data-id="<?php echo $id ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19" viewBox="0 0 20 19" fill="none">
                    <line x1="1.35355" y1="0.646447" x2="19.3535" y2="18.6464" stroke="#F9FAF9" />
                    <line y1="-0.5" x2="25.4558" y2="-0.5" transform="matrix(0.707107 -0.707106 0.707107 0.707106 1 19)"
                          stroke="#F9FAF9" />
                </svg>
            </button>
        </aside>
        <br>
    </div>
<?php endforeach; ?>
