<?php

/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @since       1.8
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @subpackage  Classes/Give_Settings_Gateways
 */

use Give\DonationForms\V2\DonationFormsAdminPage;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (! class_exists('Give_Settings_Gateways')) :

    /**
     * Give_Settings_Gateways.
     *
     * @sine 1.8
     */
    class Give_Settings_Gateways extends Give_Settings_Page
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id    = 'gateways';
            $this->label = esc_html__('Payment Gateways', 'give');

            $this->default_tab = 'gateways-settings';

            parent::__construct();

            // Do not use main form for this tab.
            if (give_get_current_setting_tab() === $this->id) {
                add_action('give_admin_field_gateway_notice', [$this, 'render_gateway_notice'], 10, 2);
                add_action('give_admin_field_enabled_gateways', [$this, 'render_enabled_gateways'], 10, 2);
            }
        }

        /**
         * Get settings array.
         *
         * @since  1.8
         * @return array
         */
        public function get_settings()
        {
            $settings        = [];
            $current_section = give_get_current_setting_section();

            switch ($current_section) {
                case 'offline-donations':
                    $settings = [
                        // Section 3: Offline gateway.
                        [
                            'type' => 'title',
                            'id'   => 'give_title_gateway_settings_3',
                        ],
                        [
                            'name'    => __('Collect Billing Details', 'give'),
                            'desc'    => __('If enabled, required billing address fields are added to Offline Donation forms. These fields are not required to process the transaction, but you may have a need to collect the data. Billing address details are added to both the donation and donor record in GiveWP. ', 'give'),
                            'id'      => 'give_offline_donation_enable_billing_fields',
                            'type'    => 'radio_inline',
                            'default' => 'disabled',
                            'options' => [
                                'enabled'  => __('Enabled', 'give'),
                                'disabled' => __('Disabled', 'give'),
                            ],
                        ],
                        [
                            'name'    => __('Offline Donation Instructions', 'give'),
                            'desc'    => __('The Offline Donation Instructions are a chance for you to educate the donor on how to best submit offline donations. These instructions appear directly on the form, and after submission of the form. Note: You may also customize the instructions on individual forms as needed.', 'give'),
                            'id'      => 'global_offline_donation_content',
                            'default' => give_get_default_offline_donation_content(),
                            'type'    => 'wysiwyg',
                            'options' => [
                                'textarea_rows' => 6,
                            ],
                        ],
                        [
                            'name'  => esc_html__('Offline Donations Settings Docs Link', 'give'),
                            'id'    => 'offline_gateway_settings_docs_link',
                            'url'   => esc_url('http://docs.givewp.com/offlinegateway'),
                            'title' => __('Offline Gateway Settings', 'give'),
                            'type'  => 'give_docs_link',
                        ],
                        [
                            'type' => 'sectionend',
                            'id'   => 'give_title_gateway_settings_3',
                        ],
                    ];
                    break;

                case 'gateways-settings':
                    $settings = [
                        // Section 1: Gateways.
                        [
                            'id' => 'give_title_gateway_settings_1',
                            'type' => 'title',
                        ],
                        [
                            'id' => 'gateway_notice',
                            'type' => 'gateway_notice',
                        ],
                        [
                            'name' => __('Test Mode', 'give'),
                            'desc' => __(
                                'If enabled, donations are processed through the sandbox/test accounts configured in each gateway\'s settings. This prevents having to use real money for tests. See the payment gateway documentation for instructions on configuring sandbox accounts.',
                                'give'
                            ),
                            'id' => 'test_mode',
                            'type' => 'radio_inline',
                            'default' => 'disabled',
                            'options' => [
                                'enabled' => __('Enabled', 'give'),
                                'disabled' => __('Disabled', 'give'),
                            ],
                        ],
                        [
                            'name' => __('Enabled Gateways', 'give') . ' - v2',
                            'desc' => __('Enable your payment gateway. Can be ordered by dragging.', 'give'),
                            'id' => 'gateways',
                            'type' => 'enabled_gateways',
                        ],
                        [
                            'name' => __('Enabled Gateways', 'give') . ' - v3',
                            'desc' => __('Enable your payment gateway. Can be ordered by dragging.', 'give'),
                            'id' => 'gateways_v3',
                            'type' => 'enabled_gateways_hidden',
                        ],

                        /**
                         * "Enabled Gateways" setting field contains gateways label setting but when you save gateway settings then label will not save
                         *  because this is not registered setting API and code will not recognize them.
                         *
                         * This setting will not render on admin setting screen but help internal code to recognize "gateways_label"  setting and add them to give setting when save.
                         */
                        [
                            'name' => __('Gateways Label', 'give') . ' - v2',
                            'desc' => '',
                            'id' => 'gateways_label',
                            'type' => 'gateways_label_hidden',
                        ],
                        [
                            'name' => __('Gateways Label', 'give') . ' - v3',
                            'desc' => '',
                            'id' => 'gateways_label_v3',
                            'type' => 'gateways_label_hidden',
                        ],

                        /**
                         * "Enabled Gateways" setting field contains default gateway setting but when you save gateway settings then this setting will not save
                         *  because this is not registered setting API and code will not recognize them.
                         *
                         * This setting will not render on admin setting screen but help internal code to recognize "default_gateway"  setting and add them to give setting when save.
                         */
                        [
                            'name' => __('Default Gateway', 'give') . ' - v2',
                            'desc' => __('The gateway that will be selected by default.', 'give'),
                            'id' => 'default_gateway',
                            'type' => 'default_gateway_hidden',
                        ],
                        [
                            'name' => __('Default Gateway', 'give') . ' - v3',
                            'desc' => __('The gateway that will be selected by default.', 'give'),
                            'id' => 'default_gateway_v3',
                            'type' => 'default_gateway_hidden',
                        ],

                        [
                            'name' => __('Gateways Docs Link', 'give'),
                            'id' => 'gateway_settings_docs_link',
                            'url' => esc_url('http://docs.givewp.com/settings-gateways'),
                            'title' => __('Gateway Settings', 'give'),
                            'type' => 'give_docs_link',
                        ],
                        [
                            'id' => 'give_title_gateway_settings_1',
                            'type' => 'sectionend',
                        ],
                    ];
                    break;
            }

            /**
             * Filter the payment gateways settings.
             * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
             */
            $settings = apply_filters('give_settings_gateways', $settings);

            /**
             * Filter the settings.
             *
             * @since  1.8
             *
             * @param array $settings
             */
            $settings = apply_filters('give_get_settings_' . $this->id, $settings);

            // Output.
            return $settings;
        }

        /**
         * Get sections.
         *
         * @since 2.9.0 move offline-donations to end of gateway list
         * @since 1.8
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = apply_filters(
                'give_get_sections_' . $this->id,
                [
                    'gateways-settings' => __('Gateways', 'give'),
                ]
            );

            $sections['offline-donations'] = __('Offline Donations', 'give');

            return $sections;
        }

        /**
         * @since 2.13.0
         * @return bool
         */
        private function hasPremiumPaymentGateway()
        {
            $gateways = give_get_payment_gateways();

            return (bool)apply_filters('give_gateway_upsell_notice_conditions', count($gateways) > 8);
        }

        /**
         * @since 2.13.0
         *
         * @return bool
         */
        private function canAcceptCreditCard()
        {
            return Give\Helpers\Gateways\Stripe::isAccountConfigured() ||
                   give(MerchantDetails::class)->accountIsConnected();
        }


        /**
         * Render Gateway Notice
         *
         * @since  2.3.0
         * @access public
         *
         * @param $field
         * @param $settings
         */
        public function render_gateway_notice($field, $settings)
        {
            if (! $this->hasPremiumPaymentGateway() && ! $this->canAcceptCreditCard()) {
                ?>
                <div class="give-gateways-notice">
                    <div class="give-gateways-cc-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="35"
                             height="29" viewBox="0 0 35 29">
                            <defs>
                                <path id="credit-card-a"
                                      d="M32.0772569,3.88888889 L2.92274306,3.88888889 C1.30642361,3.88888889 0,5.1953125 0,6.80555556 L0,28.1944444 C0,29.8046875 1.30642361,31.1111111 2.92274306,31.1111111 L32.0772569,31.1111111 C33.6935764,31.1111111 35,29.8046875 35,28.1944444 L35,6.80555556 C35,5.1953125 33.6935764,3.88888889 32.0772569,3.88888889 Z M3.28732639,6.80555556 L31.7126736,6.80555556 C31.9131944,6.80555556 32.0772569,6.96961806 32.0772569,7.17013889 L32.0772569,9.72222222 L2.92274306,9.72222222 L2.92274306,7.17013889 C2.92274306,6.96961806 3.08680556,6.80555556 3.28732639,6.80555556 Z M31.7126736,28.1944444 L3.28732639,28.1944444 C3.08680556,28.1944444 2.92274306,28.0303819 2.92274306,27.8298611 L2.92274306,17.5 L32.0772569,17.5 L32.0772569,27.8298611 C32.0772569,28.0303819 31.9131944,28.1944444 31.7126736,28.1944444 Z M11.6666667,22.1180556 L11.6666667,24.5486111 C11.6666667,24.9496528 11.3385417,25.2777778 10.9375,25.2777778 L6.5625,25.2777778 C6.16145833,25.2777778 5.83333333,24.9496528 5.83333333,24.5486111 L5.83333333,22.1180556 C5.83333333,21.7170139 6.16145833,21.3888889 6.5625,21.3888889 L10.9375,21.3888889 C11.3385417,21.3888889 11.6666667,21.7170139 11.6666667,22.1180556 Z M23.3333333,22.1180556 L23.3333333,24.5486111 C23.3333333,24.9496528 23.0052083,25.2777778 22.6041667,25.2777778 L14.3402778,25.2777778 C13.9392361,25.2777778 13.6111111,24.9496528 13.6111111,24.5486111 L13.6111111,22.1180556 C13.6111111,21.7170139 13.9392361,21.3888889 14.3402778,21.3888889 L22.6041667,21.3888889 C23.0052083,21.3888889 23.3333333,21.7170139 23.3333333,22.1180556 Z" />
                            </defs>
                            <g fill="none" fill-rule="evenodd" opacity=".341" transform="translate(0 -3)">
                                <mask id="credit-card-b" fill="#fff">
                                    <use xlink:href="#credit-card-a" />
                                </mask>
                                <g fill="#242A42" mask="url(#credit-card-b)">
                                    <rect width="35" height="35" />
                                </g>
                            </g>
                        </svg>
                    </div>

                    <p class="give-gateways-notice-title">
                        <strong>
                            <?php esc_html_e(
                                'Want to accept credit card donations directly on your website?',
                                'give'
                            ); ?>
                        </strong>
                    </p>

                    <p class="give-gateways-notice-message">
                        <?php
                        printf(
                            __(
                                'Activate the free Stripe payment gateway %1$s, <a href="%2$s" target="_blank">PayPal Donations</a>, or a premium gateway like <a href="%3$s" target="_blank">Authorize.net</a>, or <a href="%4$s" target="_blank">Stripe Premium</a> for no added fees and priority support.',
                                'give'
                            ),
                            Give()->tooltips->render_help(
                                __(
                                    'The free version of Stripe includes an additional 2% processing fee in addition to Stripe\'s normal fees for one-time donations. This ensures we can fully support the plugin for the future. Upgrade to the premium Stripe add-on for no added fees.',
                                    'give'
                                )
                            ),
                            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal'),
                            'https://givewp.com/addons/authorize-net-gateway/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner',
                            'https://givewp.com/addons/stripe-gateway/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner'
                        );
                        ?>
                    </p>

                    <div class="give-gateways-notice-button">
                        <?php echo give(AccountManagerSettingField::class)->getStripeConnectButtonMarkup(); ?>
                        <a href="https://givewp.com/addons/category/payment-gateways/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner"
                           target="_blank" class="give-view-gateways-btn button">
                            <?php esc_html_e('View Premium Gateways', 'give'); ?>
                        </a>
                    </div>
                </div>
                <?php
            }
        }

        /**
         * Render enabled gateways
         *
         * @since 3.15.0 Set the v3 settings tab as default in the gateways list
         * @since 3.0.0 split gateways into separated tabs for v2 and v3 settings
         * @since  2.0.5
         * @access public
         *
         * @param $field
         * @param $settings
         */
        public function render_enabled_gateways($field, $settings)
        {
            $id = $field['id'];
            $gateways = give_get_payment_gateways();

            $current_page = give_get_current_setting_page();
            $current_tab = give_get_current_setting_tab();
            $current_section = give_get_current_setting_section();

            // Legacy gateways are gateways that are not registered with updated gateway registration API.
            // For example: Razorpay, Paytm, Mollie etc.
            // These payment gateways support donation processing only with v2 donation forms.
            $legacyGateways = array_filter(
                $gateways,
                static function ($value, $key) {
                    return ! give()->gateways->hasPaymentGateway($key);
                },
                ARRAY_FILTER_USE_BOTH
            );

            // v2 gateways are gateways that are registered with updated gateway registration API.
            // These payment gateways support donation processing with v2 donation forms.
            // Legacy payment gateways will display with v2 gateways.
            $v2Gateways = give_get_ordered_payment_gateways(
                array_merge(
                    $legacyGateways,
                    array_intersect_key(
                        $gateways,
                        give()->gateways->getPaymentGateways(2)
                    )
                ),
                2
            );

            // v3 gateways are gateways that are registered with updated gateway registration API.
            // These payment gateways support donation processing with v3 donation forms.
            $v3Gateways = give_get_ordered_payment_gateways(
                array_intersect_key($gateways, give()->gateways->getPaymentGateways(3)),
                3
            );

            $groups = [
                'v3' => [
                    'label' => __('Visual Form Builder', 'give'),
                    'gateways' => $v3Gateways,
                    'settings' => give_get_option('gateways_v3', []),
                    'gatewaysLabel' => give_get_option('gateways_label_v3', []),
                    'defaultGateway' => give_get_option('default_gateway_v3', current(array_keys($v3Gateways))),
                    'helper' => [
                        'text' => __(
                            'Uses the blocks-based visual form builder for creating and customizing a donation form.',
                            'give'
                        ),
                        'image' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/give-settings-gateways-v3.jpg',
                    ]
                ],
                'v2' => [
                    'label' => __('Option-Based Form Editor', 'give'),
                    'gateways' => $v2Gateways,
                    'settings' => $settings,
                    'gatewaysLabel' => give_get_option('gateways_label', []),
                    'defaultGateway' => give_get_option('default_gateway', current(array_keys($v2Gateways))),
                    'helper' => [
                        'text' => __(
                            'Uses the traditional settings options for creating and customizing a donation form.',
                            'give'
                        ),
                        'image' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/give-settings-gateways-v2.jpg',
                    ],
                ],
            ];

            /**
             * @since 3.18.0
             */
            $groups = apply_filters('give_settings_payment_gateways_menu_groups', $groups);

            $defaultGroup = current(array_keys($groups));

            ob_start();

            echo '<h4>' . __('Enabled Gateways', 'give') . '</h4>';
            echo '<div class="give-settings-section-content give-payment-gateways-settings">';

            if (count($groups) > 1) {
                echo '<div class="give-settings-section-group-menu">';
                echo '<ul>';
                foreach ($groups as $slug => $group) {
                    $current_group = ! empty($_GET['group']) ? give_clean($_GET['group']) : $defaultGroup;
                    $active_class = ($slug === $current_group) ? 'active' : '';

                    if ($group['helper']) {
                        $helper = sprintf(
                            '<div class="give-settings-section-group-helper">
                                <img src="%1$s" />
                                <div class="give-settings-section-group-helper__popout">
                                    <img src="%2$s" />
                                    <h5>%3$s</h5>
                                    <p>%4$s</p>
                                </div>
                            </div>',
                            esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/help-circle.svg'),
                            esc_url($group['helper']['image']),
                            esc_html($group['label']),
                            esc_html($group['helper']['text'])
                        );
                    }

                    echo sprintf(
                        '<li><a class="%1$s" href="%2$s" data-group="%3$s">%4$s %5$s</a></li>',
                        esc_html($active_class),
                        esc_url(
                            admin_url(
                                "edit.php?post_type=give_forms&page={$current_page}&tab={$current_tab}&section={$current_section}&group={$slug}"
                            )
                        ),
                        esc_html($slug),
                        esc_html($group['label']),
                        $helper ?? ''
                    );
                }
                echo '</ul>';
                echo '</div>';
            }

            echo '<div class="give-settings-section-group-content">';
            foreach ($groups as $slug => $group) :
                $current_group = !empty($_GET['group']) ? give_clean($_GET['group']) : $defaultGroup;
                $hide_class = $slug !== $current_group ? 'give-hidden' : '';
                $suffix = $slug === 'v3' ? '_v3' : '';

                printf(
                    '<div id="give-settings-section-group-%1$s" class="give-settings-section-group %2$s">',
                    esc_attr($slug),
                    esc_html($hide_class)
                );

                echo '<div class="gateway-enabled-wrap">';
                echo '<div class="gateway-enabled-settings-title">';
                printf(
                    '
                            <span></span>
                            <span>%1$s</span>
                            <span>%2$s</span>
                            <span style="text-align: center;">%3$s</span>
                            <span style="text-align: center;">%4$s</span>
                            ',
                    __('Gateway', 'give'),
                    __('Label', 'give'),
                    __('Default', 'give'),
                    __('Enabled', 'give')
                );
                echo '</div>';

                echo '<ul class="give-checklist-fields give-payment-gatways-list">';
                foreach ($group['gateways'] as $key => $option) :
                    $enabled = null;
                    if (is_array($group['settings']) && array_key_exists($key, $group['settings'])) {
                        $enabled = '1';
                    }

                    echo '<li>';
                    printf('<span class="give-drag-handle"><span class="dashicons dashicons-menu"></span></span>');
                    printf(
                        '<span class="admin-label">%1$s %2$s</span>',
                        esc_html($option['admin_label']),
                        !empty($option['admin_tooltip']) ? Give()->tooltips->render_help(
                            esc_attr($option['admin_tooltip'])
                        ) : ''
                    );

                    $label = '';
                    if (!empty($group['gatewaysLabel'][$key])) {
                        $label = $group['gatewaysLabel'][$key];
                    }

                    printf(
                        '<input class="checkout-label" type="text" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" placeholder="%4$s"/>',
                        'gateways_label' . $suffix,
                        esc_attr($key),
                        esc_html($label),
                        esc_html($option['checkout_label'])
                    );

                    printf(
                        '<input class="gateways-radio" type="radio" name="%1$s" value="%2$s" %3$s %4$s>',
                        'default_gateway' . $suffix,
                        $key,
                        checked($key, $group['defaultGateway'], false),
                        disabled(null, $enabled, false)
                    );

                    printf(
                        '<input class="gateways-checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" type="checkbox" value="1" %3$s data-payment-gateway="%4$s"/>',
                        esc_attr($id) . $suffix,
                        esc_attr($key),
                        checked('1', $enabled, false),
                        esc_html($option['admin_label'])
                    );
                    echo '</li>';
                endforeach;
                echo '</ul>';

                echo '</div>'; // end gateway-enabled-wrap.
                echo '</div>'; // end give-settings-section-group-content.
            endforeach;

            echo '</div>'; // end give-settings-section-content.

            printf('<tr><td colspan="2" style="padding: 0">%s</td></tr>', ob_get_clean());

            $this->maybeRenderNoticeDialog();
        }

        /**
         * @since 3.0.0
         */
        private function maybeRenderNoticeDialog()
        {
            $noticeVersion = '3.0.0';
            $hasUserSeenGatewayNotice = version_compare(
                get_user_meta(
                    get_current_user_id(),
                    'give-payment-gateways-settings-dialog-read',
                    true
                ),
                $noticeVersion,
                '>='
            );

            if ($hasUserSeenGatewayNotice) {
                return;
            }

            update_user_meta(
                get_current_user_id(),
                'give-payment-gateways-settings-dialog-read',
                $noticeVersion
            );

            $supportedGateways = (new DonationFormsAdminPage())->getSupportedGateways();
            ?>

            <dialog class="give-payment-gateway-settings-dialog" id="give-payment-gateway-settings-dialog">
                <div class="give-payment-gateway-settings-dialog__header">
                    <?php
                    _e('Feature notice', 'give'); ?>
                    <button
                        class="give-payment-gateway-settings-dialog__close"
                        id="give-payment-gateway-settings-dialog__close"
                        aria-label="<?php
                        esc_attr_e('Close dialog', 'give'); ?>"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24"
                             aria-label="Close dialog icon">
                            <path
                                d="M18.707 6.707a1 1 0 0 0-1.414-1.414L12 10.586 6.707 5.293a1 1 0 0 0-1.414 1.414L10.586 12l-5.293 5.293a1 1 0 1 0 1.414 1.414L12 13.414l5.293 5.293a1 1 0 0 0 1.414-1.414L13.414 12l5.293-5.293z"></path>
                        </svg>
                    </button>
                </div>
                <div class="give-payment-gateway-settings-dialog__content">
                    <div class="give-payment-gateway-settings-dialog__content-title">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5.5 2a1 1 0 0 0-2 0v1.5H2a1 1 0 0 0 0 2h1.5V7a1 1 0 0 0 2 0V5.5H7a1 1 0 0 0 0-2H5.5V2zM5.5 17a1 1 0 1 0-2 0v1.5H2a1 1 0 1 0 0 2h1.5V22a1 1 0 1 0 2 0v-1.5H7a1 1 0 1 0 0-2H5.5V17zM13.933 2.641a1 1 0 0 0-1.866 0L10.332 7.15c-.3.78-.394 1.006-.523 1.188a2 2 0 0 1-.471.47c-.182.13-.407.224-1.188.524L3.64 11.067a1 1 0 0 0 0 1.866l4.509 1.735c.78.3 1.006.394 1.188.523.182.13.341.29.47.471.13.182.224.407.524 1.188l1.735 4.509a1 1 0 0 0 1.866 0l1.735-4.509c.3-.78.394-1.006.523-1.188.13-.182.29-.341.471-.47.182-.13.407-.224 1.188-.524l4.509-1.735a1 1 0 0 0 0-1.866L17.85 9.332c-.78-.3-1.006-.394-1.188-.523a2.001 2.001 0 0 1-.47-.471c-.13-.182-.224-.407-.524-1.188L13.933 2.64z"
                                fill="#F2CC0C"></path>
                        </svg>
                        <?php
                        esc_html_e('What\'s new', 'give'); ?>
                    </div>
                    <?php
                    esc_html_e(
                        'GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donations Form Builder. However, we are still working on gateway compatibility with the new forms experience.',
                        'give'
                    ); ?>
                    <?php
                    if ($supportedGateways) : ?>
                        <div class="give-payment-gateway-settings-dialog__content-title"><?php
                            esc_html_e('Supported gateways', 'give'); ?></div>
                        <div class="give-payment-gateway-settings-dialog__content-itemsContainer">
                            <?php
                            foreach ($supportedGateways as $gateway) : ?>
                                <div class="give-payment-gateway-settings-dialog__content-item">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M7.063.986a1.531 1.531 0 0 1 1.872 0l.783.601.98-.129c.69-.09 1.354.294 1.62.935l.377.913.911.376h.002c.641.267 1.025.93.935 1.62l-.13.98.602.783a1.534 1.534 0 0 1 0 1.872l-.601.783.129.98c.09.69-.294 1.354-.935 1.62h-.002l-.91.377-.378.912a1.537 1.537 0 0 1-1.62.936l-.98-.13-.783.601a1.531 1.531 0 0 1-1.872 0l-.782-.6-.98.129a1.537 1.537 0 0 1-1.62-.936l-.377-.912-.911-.376H2.39a1.537 1.537 0 0 1-.935-1.621l.129-.98-.601-.783a1.533 1.533 0 0 1 0-1.872l.601-.782-.129-.98c-.09-.69.294-1.354.935-1.62l.002-.001.91-.376.377-.913a1.537 1.537 0 0 1 1.62-.935l.98.13.783-.602zm3.741 5.82a.667.667 0 0 0-.943-.943L7.333 8.392 6.47 7.53a.667.667 0 1 0-.943.943L6.86 9.806c.26.26.683.26.943 0l3-3z"
                                              fill="#459948"></path>
                                    </svg>
                                    <?php
                                    echo $gateway; ?>
                                </div>
                            <?php
                            endforeach; ?>
                        </div>
                    <?php
                    endif; ?>
                    <button class="give-payment-gateway-settings-dialog__content-button"><?php
                        esc_html_e('Got it', 'give'); ?></button>
                    <a href="https://docs.givewp.com/compat-guide" rel="noopener noreferrer" target="_blank"
                       class="give-payment-gateway-settings-dialog__content-link"><?php
                        esc_html_e('Read more on Add-ons and Gateways compatibility', 'give'); ?></a>
                </div>
            </dialog>

            <?php
        }
    }

endif;

return new Give_Settings_Gateways();
