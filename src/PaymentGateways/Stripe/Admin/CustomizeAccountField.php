<?php

namespace Give\PaymentGateways\Stripe\Admin;

/**
 * Class CustomizeAccountField
 *
 * @package Give\PaymentGateways\Stripe\Admin
 * @since 2.13.0
 */
class CustomizeAccountField
{
    const DEFAULT_VALUE = 'disabled';

    /**
     * CustomizeAccountField constructor.
     */
    public function __construct()
    {
    }

    /**
     * Render
     *
     * @since 2.13.0
     */
    public function handle()
    {
        ?>
        <div class="give-stripe-manage-account-wrapper">
            <?php
            $this->getIntroductionSectionMarkup(); ?>
            <?php
            $this->getRadioButtons(); ?>
        </div>
        <?php
    }

    /**
     * @since 2.13.0
     */
    private function getIntroductionSectionMarkup()
    {
        ?>
        <div class="give-stripe-per-form-main-heading">
            <h2><?php
                esc_html_e(
                    'Which Stripe account would you like to use to process donations for this form?',
                    'give'
                ); ?></h2>
        </div>
        <?php
    }

    /**
     * @since 2.13.0
     */
    private function getRadioButtons()
    {
        $value = $this->getValue();
        ?>
        <div class="give-stripe-per-form-options">
            <div class="give-stripe-per-form-option-field give-stripe-boxshadow-option-wrap <?php
            echo 'disabled' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
                <div class="give-stripe-account-default-checkmark">
                    <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z"
                            fill="#69B868" />
                    </svg>
                </div>
                <label class="give-stripe-per-form-option-label">
                    <input
                        name="give_stripe_per_form_accounts"
                        value="disabled"
                        type="radio"
                        style="display:none;"
                        <?php
                        checked('disabled', $value); ?>
                    >
                    <span class="give-stripe-per-form-radio-title"><?php
                        esc_html_e('Default Account', 'give'); ?></span>
                    <span class="give-stripe-per-form-description">
						<?php
                        echo sprintf(
                            '%1$s <a href="%2$s" target="_blank">Global Settings</a>.',
                            esc_html__('All donations are processed through the default account set in the', 'give'),
                            give_stripe_get_admin_settings_page_url()
                        );
                        ?>
					</span>
                    <span class="give-stripe-per-form-global-setting">
						<span class="give-stripe-per-form-global-setting__title"><?php
                            esc_html_e('Default account name:', 'give'); ?></span>
						<span class="give-stripe-per-form-global-setting__name">
								<?php
                                // Output Globally set account
                                $globalAccount = give_stripe_get_default_account();

                                echo ! empty($globalAccount['account_name']) ? $globalAccount['account_name'] : esc_html__(
                                    'None set',
                                    'give'
                                );
                                echo ! empty($globalAccount['account_slug']) ? ' (' . $globalAccount['account_slug'] . ')' : '';
                                ?>
						</span>

					</span>

                </label>
            </div>

            <div class="give-stripe-per-form-option-field give-stripe-boxshadow-option-wrap <?php
            echo 'enabled' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
                <div class="give-stripe-account-default-checkmark">
                    <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z"
                            fill="#69B868" />
                    </svg>
                </div>
                <label class="give-stripe-per-form-option-label">
                    <input
                        name="give_stripe_per_form_accounts"
                        value="enabled"
                        type="radio"
                        style="display: none;"
                        <?php
                        checked('enabled', $value); ?>
                    >
                    <span class="give-stripe-per-form-radio-title"><?php
                        esc_html_e('Customize Stripe Account', 'give'); ?></span>
                    <span class="give-stripe-per-form-description">
						<?php
                        esc_html_e(
                            'Donations are processed through a Stripe account custom to this donation form. The default account is overridden for this form.',
                            'give'
                        );
                        ?>
					</span>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * @since 2.13.0
     * @return string
     */
    private function getValue()
    {
        global $post;

        $value = give()->form_meta->get_meta($post->ID, 'give_stripe_per_form_accounts', true);

        return $value ?: self::DEFAULT_VALUE;
    }
}
