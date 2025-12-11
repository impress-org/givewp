<?php
/**
 * @var $elements
 * @var $backgroundUrl
 * @var $badgeIconUrl
 * @var $heartIconUrl
 * @var $exitIconUrl
 * @var $campaignsPageUrl
 */

?>

<div <?php echo $elements->customWrapperAttributes; ?>
    class='givewp-campaign-welcome-banner-background'
    style='background-image: url("<?php echo esc_url($backgroundUrl); ?>"); no-repeat right;'>

    <div class='givewp-campaign-welcome-banner'>
        <div class='givewp-campaign-welcome-banner__actions'>
            <div class='givewp-campaign-welcome-banner__actions__badge'>
                <img src='<?php echo esc_url($badgeIconUrl); ?>' alt='badge' /> <?php echo esc_html__('NEW', 'give'); ?>
            </div>
            <button type='button' class='givewp-campaign-welcome-banner__actions__dismiss'
                <?php echo $elements->closeAttributes(); ?>>
                <img src='<?php echo esc_url($exitIconUrl); ?>' alt='exit'>
            </button>
        </div> <!-- End actions -->

        <h2 class='givewp-campaign-welcome-banner__title'>
            <?php echo esc_html__('Introducing Campaigns in Give 4.0 !', 'give'); ?> &nbsp; 🎉
        </h2>
        <p class='givewp-campaign-welcome-banner__description'>
            <?php echo esc_html__('Say hello to a whole new way to supercharge your fundraising!', 'give'); ?> &nbsp;🚀
        </p>

        <div class='givewp-campaign-welcome-banner__features'>
            <div class='givewp-campaign-welcome-banner__features__group'>
                <div class='givewp-campaign-welcome-banner__features__group__item'>
                    <img src='<?php echo esc_url($heartIconUrl); ?>' alt='heart'>
                    <span><?php echo esc_html__(
                            'Easily create, manage, and level up your campaigns.',
                            'give'
                        ); ?></span>
                </div>

                <div class='givewp-campaign-welcome-banner__features__group__item'>
                    <img src='<?php echo esc_url($heartIconUrl); ?>' alt='heart'>
                    <span><?php echo esc_html__('Enjoy tailored reports—all in one awesome place!', 'give'); ?></span>
                </div>
            </div> <!-- End features group -->

            <div class='givewp-campaign-welcome-banner__features__group'>
                <div class='givewp-campaign-welcome-banner__features__group__item'>
                    <img src='<?php echo esc_url($heartIconUrl); ?>' alt='heart'>
                    <span><?php echo esc_html__('Add as many donation forms as you want.', 'give'); ?></span>
                </div>

                <div class='givewp-campaign-welcome-banner__features__group__item'>
                    <img src='<?php echo esc_url($heartIconUrl); ?>' alt='heart'>
                    <span><?php echo esc_html__('Update your campaigns on the fly.', 'give'); ?></span>
                </div>
            </div> <!-- End features group -->
        </div> <!-- End features -->

        <a class='givewp-campaign-welcome-banner__cta-button' href='<?php echo esc_url($campaignsPageUrl); ?>'>
            <span class='givewp-campaign-welcome-banner'>
                <?php echo esc_html__('Explore campaigns', 'give'); ?>
            </span>
        </a>
    </div> <!-- End campaign welcome banner -->
</div> <!-- End campaign welcome banner background -->
