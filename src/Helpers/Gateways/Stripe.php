<?php

namespace Give\Helpers\Gateways;

/**
 * Class Stripe
 *
 * @package Give\Helpers\Gateways
 */
class Stripe
{

    /**
     * Check whether the Account is configured or not.
     *
     * @since  2.7.0
     * @access public
     *
     * @return bool
     */
    public static function isAccountConfigured()
    {
        $publishableKey = give_stripe_get_publishable_key();
        $secretKey = give_stripe_get_secret_key();

        return ! empty($publishableKey) || ! empty($secretKey);
    }

    /**
     * This function is used to add Stripe account details to donation if donation process with any stripe payment method.
     *
     * @since 2.7.0
     *
     * @param int $formId
     *
     * @param int $donationId
     */
    public static function addAccountDetail($donationId, $formId)
    {
        $accountId = give_stripe_get_default_account_slug($formId);
        $accountDetail = give_stripe_get_default_account($formId);
        $accountName = 'connect' === $accountDetail['type'] ? $accountDetail['account_name'] : give_stripe_convert_slug_to_title(
            $accountId
        );

        $stripeAccountNote = 'connect' === $accountDetail['type'] ?
            sprintf(
                '%1$s "%2$s" %3$s',
                esc_html__('Donation accepted with Stripe account', 'give'),
                "{$accountName} ({$accountId})",
                esc_html__('using Stripe Connect.', 'give')
            ) :
            sprintf(
                '%1$s "%2$s" %3$s',
                esc_html__('Donation accepted with Stripe account', 'give'),
                $accountName,
                esc_html__('using Manual API Keys.', 'give')
            );

        give_update_meta($donationId, '_give_stripe_account_slug', $accountId);

        // Log data to donation notes.
        give_insert_payment_note($donationId, $stripeAccountNote);
    }

    /**
     * Return whether or not a Stripe payment method.
     *
     * @param $paymentMethod
     *
     * @return bool
     */
    public static function isDonationPaymentMethod($paymentMethod)
    {
        return in_array($paymentMethod, give_stripe_supported_payment_methods(), true);
    }

    /**
     * Show Stripe Credit Card Fields.
     *
     * @since  2.8.0
     * @access public
     *
     * @param string $idPrefix ID Prefix to define same forms uniquely.
     *
     * @return mixed|void
     */
    public static function showCreditCardFields($idPrefix)
    {
        ob_start();
        $ccFieldFormat = give_get_option('stripe_cc_fields_format', 'multi');

        if ('single' === $ccFieldFormat) {
            // Display the stripe container which can be occupied by Stripe for CC fields.
            echo sprintf(
                '<div id="%1$s" class="give-stripe-single-cc-field-wrap"></div>',
                "give-stripe-single-cc-fields-{$idPrefix}"
            );
        } elseif ('multi' === $ccFieldFormat) {
            ?>
            <div id="give-card-number-wrap-<?php
            echo esc_html($idPrefix); ?>"
                 class="form-row form-row-two-thirds form-row-responsive give-stripe-cc-field-wrap">
                <div>
                    <label for="give-card-number-field-<?php
                    echo esc_html($idPrefix); ?>" class="give-label">
                        <?php
                        esc_attr_e('Card Number', 'give'); ?>
                        <span class="give-required-indicator">*</span>
                        <span class="give-tooltip give-icon give-icon-question"
                              data-tooltip="<?php
                              esc_attr_e(
                                  'The (typically) 16 digits on the front of your credit card.',
                                  'give'
                              ); ?>"></span>
                        <span class="card-type"></span>
                    </label>
                    <div id="give-card-number-field-<?php
                    echo esc_html($idPrefix); ?>"
                         class="input empty give-stripe-cc-field give-stripe-card-number-field"></div>
                </div>
            </div>

            <div id="give-card-cvc-wrap-<?php
            echo esc_html($idPrefix); ?>"
                 class="form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
                <div>
                    <label for="give-card-cvc-field-<?php
                    echo esc_html($idPrefix); ?>" class="give-label">
                        <?php
                        esc_attr_e('CVC', 'give'); ?>
                        <span class="give-required-indicator">*</span>
                        <span class="give-tooltip give-icon give-icon-question"
                              data-tooltip="<?php
                              esc_attr_e(
                                  'The 3 digit (back) or 4 digit (front) value on your card.',
                                  'give'
                              ); ?>"></span>
                    </label>
                    <div id="give-card-cvc-field-<?php
                    echo esc_html($idPrefix); ?>"
                         class="input empty give-stripe-cc-field give-stripe-card-cvc-field"></div>
                </div>
            </div>

            <div id="give-card-name-wrap-<?php
            echo esc_html($idPrefix); ?>" class="form-row form-row-two-thirds form-row-responsive">
                <label for="give-card-name-field-<?php
                echo esc_html($idPrefix); ?>" class="give-label">
                    <?php
                    esc_attr_e('Cardholder Name', 'give'); ?>
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                          data-tooltip="<?php
                          esc_attr_e('The name of the credit card account holder.', 'give'); ?>"></span>
                </label>
                <input
                    type="text"
                    autocomplete="off"
                    id="give-card-name-field-<?php
                    echo esc_html($idPrefix); ?>"
                    name="card_name"
                    class="card-name give-input required"
                    placeholder="<?php
                    esc_attr_e('Cardholder Name', 'give'); ?>"
                />
            </div>

            <?php
            do_action('give_before_cc_expiration'); ?>

            <div id="give-card-expiration-wrap-<?php
            echo esc_html($idPrefix); ?>"
                 class="card-expiration form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
                <div>
                    <label for="give-card-expiration-field-<?php
                    echo esc_html($idPrefix); ?>" class="give-label">
                        <?php
                        esc_attr_e('Expiration', 'give'); ?>
                        <span class="give-required-indicator">*</span>
                        <span class="give-tooltip give-icon give-icon-question"
                              data-tooltip="<?php
                              esc_attr_e(
                                  'The date your credit card expires, typically on the front of the card.',
                                  'give'
                              ); ?>"></span>
                    </label>

                    <div id="give-card-expiration-field-<?php
                    echo esc_html($idPrefix); ?>"
                         class="input empty give-stripe-cc-field give-stripe-card-expiration-field"></div>
                </div>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Collect Billing Address?
     *
     * @since 2.8.0
     *
     * @return bool
     */
    public static function collectBillingAddress()
    {
        return give_is_setting_enabled(give_get_option('stripe_collect_billing'));
    }

    /**
     * Can Show Billing address Fields.
     *
     * @since 2.8.0
     *
     * @param array $args List of additional arguments.
     *
     * @param int   $formId Donation Form ID.
     *
     * @return bool|mixed
     */
    public static function canShowBillingAddress($formId, $args)
    {
        if ( ! self::collectBillingAddress()) {
            remove_action('give_after_cc_fields', 'give_default_cc_address_fields');

            return false;
        }

        do_action('give_after_cc_fields', $formId, $args);

        return true;
    }
}
