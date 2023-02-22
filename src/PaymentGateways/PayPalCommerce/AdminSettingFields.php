<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give_HTML_Elements;
use Give_License;

/**
 * Class AdminSettingFields
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class AdminSettingFields
{
    /**
     * @var MerchantDetail
     */
    private $merchantModel;

    /**
     * @var Settings
     */
    private $settingRepository;

    /**
     * @var MerchantDetails
     */
    private $merchantRepository;

    /**
     * AdminSettingFields constructor.
     *
     * @param MerchantDetail $merchantDetail
     * @param MerchantDetails $merchantDetailRepository
     * @param Settings $settings
     */
    public function __construct(
        MerchantDetail $merchantDetail,
        MerchantDetails $merchantDetailRepository,
        Settings $settings
    ) {
        $this->merchantModel = $merchantDetail;
        $this->merchantRepository = $merchantDetailRepository;
        $this->settingRepository = $settings;
    }

    /**
     * Bootstrap fields.
     *
     * @since 2.9.0
     */
    public function boot()
    {
        add_action('give_admin_field_paypal_commerce_account_manger', [$this, 'payPalCommerceAccountManagerField']);
        add_action('give_admin_field_paypal_commerce_account_country', [$this, 'accountCountryField']);
        add_action('give_admin_field_paypal_commerce_introduction', [$this, 'introductionSection']);
    }

    /**
     * Render account country field.
     *
     * @since 2.9.0
     */
    public function accountCountryField()
    {
        /* @var Give_HTML_Elements $htmlElements */
        $htmlElements = give('html');

        $settingHtml = $htmlElements->select(
            [
                'id' => 'paypal_commerce_account_country',
                'options' => give_get_country_list(),
                'chosen' => true,
                'placeholder' => esc_html__('Choose a country', 'give'),
                'show_option_all' => false,
                'show_option_none' => false,
                'data' => [
                    'search-type' => 'no_ajax',
                ],
                'selected' => $this->merchantModel->accountCountry ?: $this->settingRepository->getAccountCountry(),
            ]
        );

        $trClass = $this->merchantRepository->accountIsConnected() ?
            'js-fields-has-custom-saving-logic hide-with-position' :
            'js-fields-has-custom-saving-logic';
        ?>
        <tr valign="top" class="<?php
        echo $trClass; ?>">
            <th scope="row" class="titledesc">
                <label for="give_paypal_commerce_country"><?php
                    esc_html_e('Account Country', 'give'); ?></label>
            </th>
            <td class="give-forminp">
                <?php
                printf(
                    '%1$s<div class="give-field-description">%2$s</div>',
                    $settingHtml,
                    esc_html__('The country your site operates from.', 'give')
                )
                ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Paypal Checkout account manager custom field
     *
     * @since 2.9.0
     */
    public function payPalCommerceAccountManagerField()
    {
        $recurringAddonInfo = Give_License::get_plugin_by_slug('give-recurring');
        $isRecurringAddonActive = isset($recurringAddonInfo['Status']) && 'active' === $recurringAddonInfo['Status'];
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="give_paypal_commerce_country"><?php
                    esc_html_e('PayPal Connection', 'give'); ?></label>
            </th>
            <td class="give-forminp">
                <div id="give-paypal-commerce-account-manager-field-wrap">
                    <div class="connect-button-wrap">
                        <?php
                        /** @var MerchantDetails $accountRepository */
                        $accountRepository = give(MerchantDetails::class);
                        ?>
                        <div class="button-wrap connection-setting <?php
                        echo $accountRepository->accountIsConnected() ? 'give-hidden' : ''; ?>">
                            <div>
                                <button class="button button-primary button-large"
                                        id="js-give-paypal-on-boarding-handler">
                                    <i class="fab fa-paypal"></i>&nbsp;&nbsp;
                                    <?php
                                    esc_html_e(
                                        'Connect with PayPal',
                                        'give'
                                    );
                                    ?>
                                </button>
                                <a class="give-hidden" target="_blank"
                                   data-paypal-onboard-complete="givePayPalOnBoardedCallback" href="#"
                                   data-paypal-button="true">
                                    <?php
                                    esc_html_e('Sign up for PayPal', 'give'); ?>
                                </a>
                                <span class="tooltip">
							<span class="left-arrow"></span>
							<?php
                            esc_html_e('Click to get started!', 'give'); ?>
						</span>
                            </div>
                            <span class="give-field-description">
							<i class="fa fa-exclamation"></i>
							<?php
                            esc_html_e('PayPal is currently NOT connected.', 'give'); ?>
						</span>
                        </div>
                        <div class="button-wrap disconnection-setting <?php
                        echo ! $accountRepository->accountIsConnected() ? 'give-hidden' : ''; ?>">
                            <div>
                                <button class="button button-large disabled" disabled="disabled">
                                    <i class="fab fa-paypal"></i>&nbsp;&nbsp;<?php
                                    esc_html_e('Connected', 'give'); ?>
                                </button>
                            </div>
                            <div>
						<span class="give-field-description">
							<i class="fa fa-check"></i>
							<?php
                            printf(
                                '%1$s <span class="paypal-account-email">%2$s</span>',
                                esc_html__('Connected for payments as', 'give'),
                                give(MerchantDetail::class)->merchantId
                            );
                            ?>
						</span>
                                <span class="actions">
							<button
                               id="js-give-paypal-disconnect-paypal-account"><?php
                                    esc_html_e('Disconnect', 'give'); ?></button>
						</span>
                            </div>
                            <div class="api-access-feature-list-wrap">
                                <p><?php
                                    esc_html_e('APIs Connected:', 'give'); ?></p>
                                <ul>
                                    <li><?php
                                        esc_html_e('Payments', 'give'); ?></li>
                                    <?php
                                    if ($isRecurringAddonActive) : ?>
                                        <li><?php
                                            esc_html_e('Subscriptions', 'give'); ?></li>
                                    <?php
                                    endif; ?>
                                    <li><?php
                                        esc_html_e('Refunds', 'give'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <?php
                        $this->printErrors(); ?>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * PayPal Commerce introduction section.
     *
     * @since 2.9.0
     */
    public function introductionSection()
    {
        ?>
        <div id="give-paypal-commerce-introduction-wrap">
            <div class="hero-section">
                <div>
                    <h2><?php
                        esc_html_e('Accept Donations with PayPal Donations', 'give'); ?></h2>
                    <p class="give-field-description"><?php
                        esc_html_e(
                            'Allow your donors to give using Debit or Credit Cards directly on your website with no additional fees.',
                            'give'
                        ); ?></p>
                </div>
                <div class="paypal-logo">
                    <img src="<?php
                    echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/paypal-logo.svg'; ?>" width="316" height="84"
                         alt="<?php
                         esc_attr_e('PayPal Logo Image', 'give'); ?>">
                </div>
            </div>
            <div class="feature-list">
                <div><i class="fa fa-angle-right"></i><?php
                    esc_html_e('Credit and Debit Card Donations', 'give'); ?>
                </div>
                <div>
                    <i class="fa fa-angle-right"></i><?php
                    esc_html_e('Improve donation conversion rates', 'give'); ?>
                </div>
                <div><i class="fa fa-angle-right"></i><?php
                    esc_html_e('Easy no-API key connection', 'give'); ?></div>
                <div>
                    <i class="fa fa-angle-right"></i><?php
                    esc_html_e('Accept payments from around the world', 'give'); ?>
                </div>
                <div><i class="fa fa-angle-right"></i><?php
                    esc_html_e('Donate via PayPal accounts', 'give'); ?>
                </div>
                <div><i class="fa fa-angle-right"></i><?php
                    esc_html_e('Supports 3D Secure payments', 'give'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Return whether or not country is in North America
     *
     * @return boolean
     */
    private function isCountryInNorthAmerica()
    {
        // Countries list: https://en.wikipedia.org/wiki/List_of_North_American_countries_by_area#Countries
        $northAmericaCountryList = [
            'CA', // Canada
            'US', // United States
            'MX', // Mexico
            'NI', // Nicaragua
            'HN', // Honduras
            'CU', // Cuba
            'GT', // Guatemala
            'PA', // Panama
            'CR', // Costa Rica
            'DO', // Dominican Republic
            'HT', // Haiti
            'BZ', // Belize
            'SV', // EL Salvador
            'BS', // The Bahamas
            'JM', // Jamaica
            'TT', // Trinidad and Tobago
            'DM', // Dominica
            'LC', // Saint Lucia
            'AG', // Antigua and Barbuda
            'BB', // Barbados
            'VC', // Saint Vincent and the Grenadines
            'GD', // Grenada
            'KN', // Saint Kitts and Nevis
        ];

        $accountCountry = $this->settingRepository->getAccountCountry();

        return in_array($accountCountry, $northAmericaCountryList, true);
    }

    /**
     * Return admin guidance notice to fix PayPal on boarding error.
     *
     * @since 2.9.6
     *
     * @param bool $completeMessage
     *
     * @return string
     */
    public function getAdminGuidanceNotice($completeMessage = true)
    {
        if ($this->isCountryInNorthAmerica()) {
            $telephone = sprintf(
                '<a href="tel:%1$s">%1$s</a>',
                '1-888-221-1161'
            );

            $message = sprintf(
                esc_html__('Please call a PayPal support representative at %1$s', 'give'),
                $telephone
            );
        } else {
            $message = esc_html__(
                'Please reach out to PayPal support from your PayPal account Resolution Center',
                'give'
            );
        }

        $message .= $completeMessage ? esc_html__(' and relay the following message:', 'give') : '.';

        return $message;
    }

    /**
     * Print on boarding errors.
     *
     * @since 2.9.6
     */
    private function printErrors()
    {
        $accountErrors = give(MerchantDetails::class)->getAccountErrors();
        $hasUnknownPayPalError = isset($_GET['paypal-error']);

        if ( ! empty($accountErrors)) :
            ?>
            <div>
                <p class="error-message"><?php
                    esc_html_e('Warning, your account is not ready to accept donations.', 'give'); ?></p>
                <p>
                    <?php
                    printf(
                        '%1$s %2$s',
                        esc_html__(
                            'There is an issue with your PayPal account that is preventing you from being able to accept donations.',
                            'give'
                        ),
                        $this->getAdminGuidanceNotice()
                    )
                    ?>
                </p>
                <div class="paypal-message-template">
                    <?php
                    esc_html_e('Greetings!', 'give'); ?><br><br>
                    <?php
                    esc_html_e(
                        'I am trying to connect my PayPal account to the GiveWP plugin for WordPress. I have gone through the onboarding process to connect my account, but when I finish I\'m given the following message from GiveWP:',
                        'give'
                    ); ?><br>
                    <?php
                    echo $this->formatErrors($accountErrors); ?>
                    <br><?php
                    esc_html_e(
                        'Please help me resolve these account errors so I can begin accepting payments via PayPal on GiveWP.',
                        'give'
                    ); ?>
                </div>

                <?php
                if ($this->merchantRepository->accountIsConnected()) : ?>
                    <p>
                        <a href="<?php
                        echo admin_url(
                            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&paypalStatusCheck'
                        ); ?>">
                            <?php
                            esc_html_e('Re-Check Account Status', 'give'); ?>
                        </a>
                    </p>
                <?php
                endif; ?>

            </div>
        <?php
        endif;
    }

    /**
     * Return format errors string.
     *
     * @since 2.9.6
     *
     * @param array $errors
     *
     * @return string
     */
    private function formatErrors($errors)
    {
        $isSingleError = ! (count($errors) > 1);
        $formattedArray = array_map(
            static function ($arr) use ($isSingleError) {
                if (is_array($arr)) {
                    switch ($arr['type']) {
                        case 'url':
                            return sprintf(
                                '<%1$s>%2$s<br><code>%3$s</code></%1$s>',
                                $isSingleError ? 'p' : 'li',
                                $arr['message'],
                                urldecode_deep($arr['value'])
                            );

                        case 'json':
                            return sprintf(
                                '<%1$s>%2$s<br><code>%3$s</code></%1$s>',
                                $isSingleError ? 'p' : 'li',
                                $arr['message'],
                                $arr['value']
                            );
                    }
                }

                return sprintf(
                    '<%1$s>%2$s</%1$s>',
                    $isSingleError ? 'p' : 'li',
                    $arr
                );
            },
            $errors
        );

        $output = implode('', $formattedArray);

        if ( ! $isSingleError) {
            $output = sprintf(
                '<ul class="ul-disc">%1$s</ul>',
                $output
            );
        }

        return $output;
    }
}
