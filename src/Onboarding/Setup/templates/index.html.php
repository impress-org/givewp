<?php
/**
 * GiveWP Onboarding Setup Guide template file
 *
 * @since 3.15.0 Refactored to make it compatible with v3 forms.
 * @since 2.8.0
 */

use Give\DonationForms\Models\DonationForm;

/**
 * Variables from onboarding PageView
 *
 * @var array $settings
 */

?>
<div class="wrap" class="give-setup-page">

    <h1 class="wp-heading-inline">
        <?php
        echo __('GiveWP Setup Guide', 'give'); ?>
    </h1>

    <hr class="wp-header-end">

    <?php
    if (isset($_GET['give_setup_stripe_error'])) : ?>
        <div class="notice notice-error">
            <p><?php
                echo esc_html($_GET['give_setup_stripe_error']); ?></p>
        </div>
    <?php
    endif; ?>

    <?php
    /**
     * Use this action to perform task before section loads.
     *
     * @since 2.10.0
     */
    do_action('give_setup_page_before_sections');
    ?>

    <!-- Configuration -->
    <?php
    if ($this->isFormConfigured()) {
        $form = DonationForm::find((int)$settings['form_id']);

        $customizeFormURL = $form && $form->id ? admin_url('post.php?action=edit&post=' . $form->id) : admin_url('edit.php?post_type=give_forms&page=give-forms');
    }

    echo $this->render_template(
        'section',
        [
            'class' => !$this->isFormConfigured() ? 'current-step' : '',
            'title' => sprintf('%s 1: %s', __('Step', 'give'), __('Create your first donation form', 'give')),
            'badge' => ($this->isFormConfigured()
                ? $this->render_template('badge', [
                    'class' => 'completed',
                    'text' => esc_html__('Completed', 'give'),
                ])
                : $this->render_template('badge', [
                    'class' => 'not-completed',
                    'text' => esc_html__('Not Completed', 'give'),
                ])
            ),
            'button' => ($this->isFormConfigured()
                ? $this->render_template('action-button', [
                    'href' => esc_url($customizeFormURL),
                    'text' => esc_html__('Customize form', 'give'),
                    'target' => '_blank',
                ])
                : $this->render_template('action-button', [
                    'href' => esc_url(admin_url('?page=give-onboarding-wizard')),
                    'text' => esc_html__('Configure GiveWP', 'give'),
                    'target' => '_blank',
                ])
            ),
        ]
    );
    ?>

    <!-- Gateways -->
    <?php
    echo $this->render_template(
        'section',
        [
            'class' => ($this->isFormConfigured() && !($this->isStripeSetup() || $this->isPayPalSetup())) ? 'current-step' : '',
            'title' => sprintf('%s 2: %s', __('Step', 'give'), __('Connect a payment gateway', 'give')),
            'badge' => (($this->isStripeSetup() || $this->isPayPalSetup())
                ? $this->render_template('badge', [
                    'class' => 'completed',
                    'text' => esc_html__('Completed', 'give'),
                ])
                : $this->render_template('badge', [
                    'class' => 'not-completed',
                    'text' => esc_html__('Not Completed', 'give'),
                ])
            ),
            'contents' => [
                $this->render_template(
                    'row-item',
                    [
                        'testId' => 'stripe',
                        'class' => ($this->isStripeSetup()) ? 'stripe setup-item-completed' : 'stripe',
                        'icon' => ($this->isStripeSetup())
                            ? $this->image('check-circle.min.png')
                            : $this->image('stripe@2x.min.png'),
                        'icon_alt' => esc_html__('Stripe', 'give'),
                        'title' => esc_html__('Connect to Stripe', 'give'),
                        'description' => esc_html__(
                            'Stripe is one of the most popular payment gateways, and for good reason! Receive one-time and Recurring Donations (add-on) using many of the most popular payment methods. Note: the FREE version of Stripe includes an additional 2% fee for processing one-time donations. Remove the fee by installing and activating the premium Stripe add-on.',
                            'give'
                        ),
                        'action' => ($this->isStripeSetup()) ? sprintf(
                            '<a href="%s"><i class="fab fa-stripe-s"></i>&nbsp;&nbsp;Stripe Settings</a>',
                            esc_url(add_query_arg(
                                [
                                    'post_type' => 'give_forms',
                                    'page' => 'give-settings',
                                    'tab' => 'gateways',
                                    'section' => 'stripe-settings',
                                ],
                                admin_url('edit.php')
                            ))
                        )
                            : sprintf(
                                '<a href="%s"><i class="fab fa-stripe-s"></i>&nbsp;&nbsp;Connect to Stripe</a>',
                                $this->stripeConnectURL()
                            ),
                    ]
                ),
               $this->render_template(
                    'row-item',
                    [
                        'testId' => 'paypal',
                        'class' => ($this->isPayPalSetup()) ? 'paypal setup-item-completed' : 'paypal',
                        'icon' => ($this->isPayPalSetup())
                            ? $this->image('check-circle.min.png')
                            : $this->image('paypal@2x.min.png'),
                        'icon_alt' => esc_html__('PayPal', 'give'),
                        'title' => esc_html__('Connect to PayPal', 'give'),
                        'description' => esc_html__(
                            'PayPal is synonymous with nonprofits and online charitable gifts. It\'s been the go-to payment merchant for many of the world\'s top NGOs. Accept PayPal, credit and debit cards without any added platform fees.',
                            'give'
                        ),
                        'action' => sprintf(
                            '<a href="%1$s"><i class="fab fa-paypal"></i>&nbsp;&nbsp;%2$s</a>',
                            esc_url(add_query_arg(
                                [
                                    'post_type' => 'give_forms',
                                    'page' => 'give-settings',
                                    'tab' => 'gateways',
                                    'section' => 'paypal',
                                    'group' => 'paypal-commerce',
                                ],
                                admin_url('edit.php')
                            )),
                            ! $this->isPayPalSetup() ? esc_html__('Connect to PayPal', 'give') : esc_html__(
                                'PayPal Settings',
                                'give'
                            )
                        ),
                    ]
                ),
            ],
            'footer' => $this->render_template(
                'footer',
                [
                    'contents' => sprintf(
                        '<img src="%s" /><p><strong>%s</strong> %s</p> %s',
                        $this->image('payment-gateway.svg'),
                        __('Explore other payment gateways:', 'give'),
                        __('GiveWP has support for many others including Authorize.net, Square, Razorpay and more!', 'give'),
                        sprintf(
                            '<a href="%s" target="_blank">%s <i class="fa fa-chevron-right" aria-hidden="true"></i></a>',
                            'http://docs.givewp.com/payment-gateways', // UTM included.
                            __('View all gateways', 'give')
                        )
                    ),
                ]
            ),
        ]
    );
    ?>

    <!-- Resources -->
    <?php
    $needsActivation = true;
    $licenses = get_option('give_licenses', []);

    foreach ($licenses as $license) {
        if (empty($license['license_key'])) {
            continue;
        }

        $licenseKey = $license['license_key'];
        $licenseStatus = $license['license'];
        $expiresTimestamp = strtotime($license['expires']);

        $isLicenseInactive = $licenseStatus !== 'valid';
        $isLicenseExpired = $licenseStatus === 'expired' || $expiresTimestamp < time();

        if (!$isLicenseInactive && !$isLicenseExpired) {
            $needsActivation = false;
            break;
        }
    }

    echo $this->render_template(
        'section',
        [
            'title' => sprintf('%s 3: %s', __('Step', 'give'), __('Get more from your fundraising campaign with add-ons', 'give')),
            'badge' => $this->render_template('badge', [
                'class' => 'optional',
                'text' => esc_html__('Optional', 'give'),
            ]),
            'contents' => [
                (! empty($settings['addons'] || $needsActivation)) ? $this->render_template(
                    'sub-header',
                    [
                        'text' => sprintf(
                            '%s%s',
                            (! empty($settings['addons']) ? esc_html__('Based on your selections, Give recommends the following add-ons to support your fundraising.', 'give') . ' ' : ''),
                            ($needsActivation ? $this->render_template('activate-license',
                                [
                                    'text' => esc_html__('Already have an add-on license?', 'give'),
                                    'label' => esc_html__('Activate your license', 'give'),
                                    'href' => esc_url(admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses')),
                                    'title' => esc_html__('Activate an Add-on License', 'give'),
                                    'description' => sprintf(
										__('Enter your license key below to unlock your GiveWP add-ons. You can access your licenses anytime from the <a href="%1$s" target="_blank">My Account</a> section on the GiveWP website. ', 'give'),
                                        Give_License::get_account_url()
                                    ),
                                    'nonce' => wp_nonce_field('give-license-activator-nonce', 'give_license_activator_nonce', true, false),
                                    'form-label' => esc_html__('License key', 'give'),
                                    'form-placeholder' => esc_html__('Enter your license key', 'give'),
                                    'form-submit-activate' => esc_html__('Activate License', 'give'),
                                    'form-submit-activating' => esc_html__('Verifying License...', 'give'),
                                    'form-submit-value' => esc_html__('Activate License', 'give'),
                                ]
                            ) : '')
                        )
                    ]
                ) : '',
                in_array('recurring-donations', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-recurring-donations',
                        'icon' => $this->image('recurring-donations@2x.min.png'),
                        'icon_alt' => __('Recurring Donations', 'give'),
                        'title' => __('Recurring Donations', 'give'),
                        'description' => __(
                            'Raise funds reliably through subscriptions based donations. Let your donors choose how often they give and how much. Manage your subscriptions, view specialized reports, and strengthen relationships with your recurring donors.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-recurring', // UTM included.
                                'label' => __('Get Recurring Donations', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                in_array('donors-cover-fees', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-fee-recovery',
                        'icon' => $this->image('fee-recovery@2x.min.png'),
                        'icon_alt' => __('Fee Recovery', 'give'),
                        'title' => __('Fee Recovery', 'give'),
                        'description' => __(
                            'Maximize your donations by allowing donors to cover payment processing fees, ensuring more funds go directly to your cause.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-fee-recovery', // UTM included.
                                'label' => __('Get Fee Recovery', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                in_array('pdf-receipts', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-pdf-receipts',
                        'icon' => $this->image('pdf-receipts@2x.min.png'),
                        'icon_alt' => __('PDF Receipts', 'give'),
                        'title' => __('PDF Receipts', 'give'),
                        'description' => __(
                            'PDF Receipts makes it easy for your donors to print their tax deductible receipts by making PDF downloadable copies of them easily available. Donors can get a link to their receipt provided to them in the confirmation email, there is also a link in the donation confirmation screen, and a link in their Donation History page.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-pdf-receipts', // UTM included.
                                'label' => __('Get PDF Receipts', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                in_array('custom-form-fields', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-form-fields-manager',
                        'icon' => $this->image('form-fields-manager@2x.min.png'),
                        'icon_alt' => __('Form Field Manager', 'give'),
                        'title' => __('Form Field Manager', 'give'),
                        'description' => __(
                            'Form Field Manager (FFM) allows you to add and manage additional fields for your GiveWP donation forms using an intuitive drag-and-drop interface. Form fields include simple fields such as checkboxes, dropdowns, radios, and more. The more complex form fields that you can add are file upload fields, Rich text editors (TinyMCE), and the powerful Repeater field.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-ffm', // UTM included.
                                'label' => __('Get Form Field Manager', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                in_array('multiple-currencies', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-currency-switcher',
                        'icon' => $this->image('currency-switcher@2x.min.png'),
                        'icon_alt' => __('Currency Switcher', 'give'),
                        'title' => __('Currency Switcher', 'give'),
                        'description' => __(
                            'Let donors choose from your selected currencies, increasing global donations with live exchange rates and extensive currency options.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-currency-switcher', // UTM included.
                                'label' => __('Get Currency Switcher', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                in_array('dedicate-donations', $settings['addons']) ? $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item-tributes',
                        'icon' => $this->image('tributes@2x.min.png'),
                        'icon_alt' => __('Tributes', 'give'),
                        'title' => __('Tributes', 'give'),
                        'description' => __(
                            'Allow donors to give to your cause via customizable tributes like “In honor of,” “In memory of,” or any dedication you prefer. Send eCards and produce customizable mailable cards that your donors and their honorees will love.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-tributes', // UTM included.
                                'label' => __('Get Tributes', 'give'),
                            ]
                        ),
                    ]
                ) : '',
                $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item',
                        'icon' => $this->image('addons@2x.min.png'),
                        'icon_alt' => esc_html__('Add-ons', 'give'),
                        'title' => esc_html__('GiveWP Add-ons', 'give'),
                        'description' => esc_html__(
                            'Boost your fundraising efforts with powerful add-ons like Recurring Donations, Fee Recovery, Google Analytics, Mailchimp, and more. Explore our extensive library of 35+ add-ons to enhance your fundraising now.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-addons', // UTM included.
                                'label' => esc_html__('View all premium add-ons', 'give'),
                            ]
                        ),
                    ]
                ),
            ],
        ]
    );
    ?>

    <?php
    echo $this->render_template(
        'section',
        [
            'title' => __('Get the most out of GiveWP', 'give'),
            'contents' => [
                $this->render_template(
                    'row-item',
                    [
                        'class' => 'setup-item',
                        'icon' => $this->image('givewp101@2x.min.png'),
                        'icon_alt' => esc_html__('GiveWP Getting Started Guide', 'give'),
                        'title' => esc_html__('GiveWP Getting Started Guide', 'give'),
                        'description' => esc_html__(
                            'Learn the basics and advanced tips to optimize your fundraising with GiveWP.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/getting-started', // UTM included.
                                'label' => __('Get started', 'give'),
                            ]
                        ),
                    ]
                ),
            ],
        ]
    );
    ?>

    <?php
    echo $this->render_template(
        'dismiss',
        [
            'action' => admin_url('admin-post.php'),
            'nonce' => wp_nonce_field('dismiss_setup_page', $name = '_wpnonce', $referer = true, $echo = false),
            'label' => esc_html__('Dismiss Setup Screen', 'give'),
        ]
    )
    ?>

</div>
