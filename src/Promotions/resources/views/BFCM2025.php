<?php
/**
 * BFCM 2025 Banner Template
 *
 * @var $elements NoticeElementProperties
 * @var $backgroundLarge string
 * @var $backgroundMedium string
 * @var $backgroundSmall string
 * @var $cartIcon string
 *
 * @since 4.11.0
 */

?>

<section <?php echo $elements->customWrapperAttributes; ?>
    id='givewp-bfcm-2025-banner'
    class='givewp-bfcm-2025-banner'
    role='banner'
    aria-label='<?php echo esc_attr__('Black Friday Cyber Monday 2025 Promotion', 'give'); ?>'
    style='--bg-small: url("<?php echo esc_url($backgroundSmall); ?>"); --bg-medium: url("<?php echo esc_url($backgroundMedium); ?>"); --bg-large: url("<?php echo esc_url($backgroundLarge); ?>");'>

    <div class='givewp-bfcm-2025-banner__content'>
        <h1 class='givewp-bfcm-2025-banner__title'><?php echo wp_kses(__('Amplify Your Impact With <span class="givewp-bfcm-2025-banner__discount">30%</span> Off', 'give'), ['span' => ['class' => []]]); ?></h1>
        <p class='givewp-bfcm-2025-banner__description'><?php echo esc_html__('Your cause deserves the best! Do more good & spend less: Nov 24 - Dec 2.', 'give'); ?></p>
        <a class='givewp-bfcm-2025-banner__cta'
           href='https://docs.givewp.com/givecore-bfcm2025'
           target='_blank'
           rel='noopener noreferrer'
           aria-label='<?php echo esc_attr__('Claim your 30% discount - opens in new tab', 'give'); ?>'>
            <img src='<?php echo esc_url($cartIcon); ?>' alt='<?php echo esc_attr__('Cart icon', 'give'); ?>' />
            <?php echo esc_html__('CLAIM MY 30% DISCOUNT', 'give'); ?>
        </a>
    </div>
</section>
