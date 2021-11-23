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
    echo $this->render_template(
        'section',
        [
            'title' => sprintf('%s 1: %s', __('Step', 'give'), __('Create your first donation form', 'give')),
            'badge' => '<span class="badge badge-review">5 Minutes</span>',
            'contents' => $this->render_template(
                'row-item',
                [
                    'testId' => 'setup-configuration',
                    'class' => ($this->isFormConfigured(
                    )) ? 'setup-item-configuration setup-item-completed' : 'setup-item-configuration',
                    'icon' => ($this->isFormConfigured())
                        ? $this->image('check-circle.min.png')
                        : $this->image('configuration@2x.min.png'),
                    'icon_alt' => esc_html__('First-Time Configuration', 'give'),
                    'title' => esc_html__('First-Time Configuration', 'give'),
                    'description' => esc_html__(
                        'Every fundraising campaign begins with a donation form. Click here to create your first donation form in minutes. Once created you can use it anywhere on your website.',
                        'give'
                    ),
                    'action' => $this->render_template(
                        'action-link',
                        [
                            'href' => admin_url('?page=give-onboarding-wizard'),
                            'screenReaderText' => 'Configure GiveWP',
                        ]
                    ),
                ]
            ),
        ]
    );
    ?>

    <!-- Gateways -->
    <?php
    echo $this->render_template(
        'section',
        [
            'title' => sprintf('%s 2: %s', __('Step', 'give'), __('Connect a payment gateway', 'give')),
            'contents' => [
                ! $this->isStripeSetup() ? $this->render_template(
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
                            add_query_arg(
                                [
                                    'post_type' => 'give_forms',
                                    'page' => 'give-settings',
                                    'tab' => 'gateways',
                                    'section' => 'paypal',
                                    'group' => 'paypal-commerce',
                                ],
                                esc_url_raw(admin_url('edit.php'))
                            ),
                            ! $this->isPayPalSetup() ? esc_html__('Connect to PayPal', 'give') : esc_html__(
                                'PayPal Settings',
                                'give'
                            )
                        ),
                    ]
                ) : '',
                ! $this->isPayPalSetup() ? $this->render_template(
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
                            add_query_arg(
                                [
                                    'post_type' => 'give_forms',
                                    'page' => 'give-settings',
                                    'tab' => 'gateways',
                                    'section' => 'stripe-settings',
                                ],
                                esc_url_raw(admin_url('edit.php'))
                            )
                        )
                            : sprintf(
                                '<a href="%s"><i class="fab fa-stripe-s"></i>&nbsp;&nbsp;Connect to Stripe</a>',
                                $this->stripeConnectURL()
                            ),
                    ]
                ) : '',
            ],
            'footer' => $this->render_template(
                'footer',
                [
                    'contents' => sprintf(
                        __(
                            'Want to use a different gateway? GiveWP has support for many others including Authorize.net, Square, Razorpay and more! %s',
                            'give'
                        ),
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
    echo $this->render_template(
        'section',
        [
            'title' => sprintf('%s 3: %s', __('Step', 'give'), __('Level up your fundraising', 'give')),
            'contents' => [
                ! empty($settings['addons']) ? $this->render_template(
                    'sub-header',
                    [
                        'text' => 'Based on your selections, Give recommends the following add-ons to support your fundraising.',
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
                            'The Recurring Donations add-on for GiveWP brings you more dependable payments by allowing your donors to give regularly at different time intervals. Let your donors choose how often they give and how much. Manage your subscriptions, view specialized reports, and connect more strategically with your recurring donors.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-recurring', // UTM included.
                                'screenReaderText' => __('Learn more about Recurring Donations', 'give'),
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
                            'Credit Card processing fees can take away a big chunk of your donations. This means less money goes to your cause. Why not ask your donors to further help your cause by asking them to take care of the payment processing fees? That’s where the Fee Recovery add-on comes into play.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-fee-recovery', // UTM included.
                                'screenReaderText' => __('Learn more about Fee Recovery', 'give'),
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
                                'screenReaderText' => __('Learn more about PDF Receipts', 'give'),
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
                                'screenReaderText' => __('Learn more about Form Field Manager', 'give'),
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
                            'Allow your donors to switch to their currency of choice and increase your overall giving with the GiveWP Currency Switcher add-on. Select from an extensive list of currencies, set the currency based on your donor\'s location, pull from live exchange rates and more!',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-currency-switcher', // UTM included.
                                'screenReaderText' => __('Learn more about Currency Switcher', 'give'),
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
                                'screenReaderText' => __('Learn more about Tributes', 'give'),
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
                            'Make your fundraising even more effective with powerful add-ons like Recurring Donations, Fee Recovery, Google Analytics Donation Tracking, MailChimp, and much more. View our growing library of 35+ add-ons and extend your fundraising now.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/setup-addons', // UTM included.
                                'screenReaderText' => __('View Add-ons for GiveWP', 'give'),
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
                            'Start off on the right foot by learning the basics of the plugin and how to get the most out of it to further your online fundraising efforts.',
                            'give'
                        ),
                        'action' => $this->render_template(
                            'action-link',
                            [
                                'target' => '_blank',
                                'href' => 'http://docs.givewp.com/getting-started', // UTM included.
                                'screenReaderText' => __('Learn more about GiveWP', 'give'),
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
