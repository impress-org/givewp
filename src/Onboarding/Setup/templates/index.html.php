<div class="wrap" class="give-setup-page">

	<h1 class="wp-heading-inline">
		<?php echo __( 'GiveWP Setup Guide', 'give' ); ?>
	</h1>

	<hr class="wp-header-end">

	<?php if ( isset( $_GET['give_setup_stripe_error'] ) ) : ?>
	<div class="notice notice-error">
		<p><?php echo esc_html( $_GET['give_setup_stripe_error'] ); ?></p>
	</div>
	<?php endif; ?>

	<!-- Configuration -->
	<?php
		echo $this->render_template(
			'section',
			[
				'title'    => sprintf( '%s 1: %s', __( 'Step', 'give' ), __( 'Create your first donation form', 'give' ) ),
				'badge'    => '<span class="badge badge-review">5-10 Minutes</span>',
				'contents' => $this->render_template(
					'row-item',
					[
						'class'       => 'setup-item-configuration',
						'icon'        => $this->image( 'configuration@2x.min.png' ),
						'icon_alt'    => esc_html__( 'First-Time Configuration', 'give' ),
						'title'       => esc_html__( 'First-Time Configuration', 'give' ),
						'description' => esc_html__( 'Every fundraising campaign begins with a donation form. Click here to create your first donation form in minutes. Once created you can use it anywhere on your website.', 'give' ),
						'action'      => $this->render_template(
							'action-link',
							[
								'href'             => admin_url( '?page=give-onboarding-wizard' ),
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
				'title'    => sprintf( '%s 2: %s', __( 'Step', 'give' ), __( 'Connect a payment gateway', 'give' ) ),
				'contents' => [
					! $this->isStripeSetup() ? $this->render_template(
						'row-item',
						[
							'class'       => 'paypal',
							'icon'        => $this->image( 'paypal@2x.min.png' ),
							'icon_alt'    => esc_html__( 'PayPal', 'give' ),
							'title'       => esc_html__( 'Connect to PayPal', 'give' ),
							'description' => esc_html__( 'PayPal is synonymous with nonprofits and online charitable gifts. It’s been the go-to payment merchant for many of the world\'s top NGOs. Accept PayPal, Credit and Debit Cards, and more using PayPal’s Smart Buttons without any added platform fees.', 'give' ),
							'action'      => sprintf(
								'<a href="%s"><i class="fab fa-paypal"></i>&nbsp;&nbsp;Connect to PayPal</a>',
								add_query_arg(
									[
										'post_type' => 'give_forms',
										'page'      => 'give-settings',
										'tab'       => 'gateways',
										'section'   => 'paypal-standard',
									],
									esc_url_raw( admin_url( 'edit.php' ) )
								)
							),
						]
					) : '',
					! $this->isStripeSetup() && ! $this->isPayPalSetup() ? $this->render_template(
						'row-item',
						[
							'class'       => 'stripe',
							'icon'        => $this->image( 'stripe-connect@2x.min.png' ),
							'icon_alt'    => esc_html__( 'Stripe', 'give' ),
							'title'       => esc_html__( 'Connect to Stripe', 'give' ),
							'description' => esc_html__( 'Stripe is one of the most popular payment gateways, and for good reason! Receive one-time and Recurring Donations (add-on) using many of the most popular payment methods. Note: the FREE version of Stripe includes an additional 2% fee for processing one-time donations.', 'give' ),
							'action'      => sprintf(
								'<a href="%s"><i class="fab fa-stripe-s"></i>&nbsp;&nbsp;Connect with Stripe</a>',
								add_query_arg(
									[
										'stripe_action' => 'connect',
										'mode'          => give_is_test_mode() ? 'test' : 'live',
										'return_url'    => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-setup' ) ),
										'website_url'   => get_bloginfo( 'url' ),
										'give_stripe_connected' => '0',
									],
									esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
								)
							),
						]
					) : '',
					$this->isStripeSetup() && ! $this->isPayPalSetup() ? $this->render_template(
						'row-item',
						[
							'class'       => 'stripe stripe-webhooks',
							'icon'        => $this->image( 'stripe-connect@2x.min.png' ),
							'icon_alt'    => esc_html__( 'Stripe', 'give' ),
							'title'       => esc_html__( 'Please configure your Stripe webhook to finalize the setup.', 'give' ),
							'description' => esc_html__( 'In order for Stripe to function properly, you must add a new Stripe webhook endpoint. To do this please visit the Webhooks Section of your Stripe Dashboard and click the Add endpoint button and paste the following URL: ', 'give' ) . '<br /><span id="stripeWebhooksCopyHandler" class="stripe-webhooks-url"><input disabled="disabled" id="stripeWebhooksCopy" value="' . add_query_arg( 'give-listener', 'stripe', site_url() ) . '" /> &nbsp; <i id="stripeWebhooksCopyIcon" class="fa fa-clipboard"></i></input>',
							'action'      =>
								sprintf( '<a id="stripeWebhooksConfigureButton" href="%s" target="_blank">%s</a>', esc_url_raw( 'https://dashboard.stripe.com/webhooks' ), __( 'Configure Webhooks', 'give' ) )
								. sprintf( '<button class="hidden" disabled="disable" id="stripeWebhooksConfigureConfirmed">%s</button>', __( 'Webhooks Configured!', 'give' ) ),
						]
					) : '',
				],
				'footer'   => $this->render_template(
					'footer',
					[
						'contents' => 'Want to use a different gateway? GiveWP has support for many others including Authorize.net, Square, Razorpay and more!<a href="#">View all gateways <i class="fa fa-chevron-right" aria-hidden="true"></i></a>',
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
				'title'    => sprintf( '%s 3: %s', __( 'Step', 'give' ), __( 'Level up your fundraising', 'give' ) ),
				'contents' => [
					in_array( 'recurring-donations', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-recurring-donations',
							'icon'        => $this->image( 'recurring-donations@2x.min.png' ),
							'icon_alt'    => __( 'Recurring Donations', 'give' ),
							'title'       => __( 'Recurring Donations', 'give' ),
							'description' => __( 'The Recurring Donations add-on for GiveWP brings you more dependable payments by allowing your donors to give regularly at different time intervals. Let your donors choose how often they give and how much. Manage your subscriptions, view specialized reports, and connect more strategically with your recurring donors.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/recurring-donations/' ),
									'screenReaderText' => __( 'Learn more about Recurring Donations', 'give' ),
								]
							),
						]
					) : '',
					in_array( 'donors-cover-fees', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-fee-recovery',
							'icon'        => $this->image( 'fee-recovery@2x.min.png' ),
							'icon_alt'    => __( 'Fee Recovery', 'give' ),
							'title'       => __( 'Fee Recovery', 'give' ),
							'description' => __( 'Credit Card processing fees can take away a big chunk of your donations. This means less money goes to your cause. Why not ask your donors to further help your cause by asking them to take care of the payment processing fees? That’s where the Fee Recovery add-on comes into play.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/fee-recovery/' ),
									'screenReaderText' => __( 'Learn more about Fee Recovery', 'give' ),
								]
							),
						]
					) : '',
					in_array( 'pdf-receipts', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-pdf-receipts',
							'icon'        => $this->image( 'pdf-receipts@2x.min.png' ),
							'icon_alt'    => __( 'PDF Receipts', 'give' ),
							'title'       => __( 'PDF Receipts', 'give' ),
							'description' => __( 'PDF Receipts makes it easy for your donors to print their tax deductible receipts by making PDF downloadable copies of them easily available. Donors can get a link to their receipt provided to them in the confirmation email, there is also a link in the donation confirmation screen, and a link in their Donation History page.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/pdf-receipts/' ),
									'screenReaderText' => __( 'Learn more about PDF Receipts', 'give' ),
								]
							),
						]
					) : '',
					in_array( 'custom-form-fields', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-form-fields-manager',
							'icon'        => $this->image( 'form-fields-manager@2x.min.png' ),
							'icon_alt'    => __( 'Form Field Manager', 'give' ),
							'title'       => __( 'Form Field Manager', 'give' ),
							'description' => __( 'Form Field Manager (FFM) allows you to add and manage additional fields for your GiveWP donation forms using an intuitive drag-and-drop interface. Form fields include simple fields such as checkboxes, dropdowns, radios, and more. The more complex form fields that you can add are file upload fields, Rich text editors (TinyMCE), and the powerful Repeater field.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/form-field-manager/' ),
									'screenReaderText' => __( 'Learn more about Form Field Manager', 'give' ),
								]
							),
						]
					) : '',
					in_array( 'multiple-currencies', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-currency-switcher',
							'icon'        => $this->image( 'currency-switcher@2x.min.png' ),
							'icon_alt'    => __( 'Currency Switcher', 'give' ),
							'title'       => __( 'Currency Switcher', 'give' ),
							'description' => __( 'Allow your donors to switch to their currency of choice and increase your overall giving with the GiveWP Currency Switcher add-on. Select from an extensive list of currencies, set the currency based on your users’ location, pull from live exchange rates and more!', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/currency-switcher/' ),
									'screenReaderText' => __( 'Learn more about Currency Switcher', 'give' ),
								]
							),
						]
					) : '',
					in_array( 'dedicate-donations', $settings['addons'] ) ? $this->render_template(
						'row-item',
						[
							'class'       => 'setup-item-tributes',
							'icon'        => $this->image( 'tributes@2x.min.png' ),
							'icon_alt'    => __( 'Tributes', 'give' ),
							'title'       => __( 'Tributes', 'give' ),
							'description' => __( 'Allow donors to give to your cause via customizable tributes like “In honor of,” “In memory of,” or any dedication you prefer. Also, you are able to send eCards and produce customizable mailable cards that your donors and their honorees will love.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/tributes/' ),
									'screenReaderText' => __( 'Learn more about Tributes', 'give' ),
								]
							),
						]
					) : '',
					$this->render_template(
						'row-item',
						[
							'icon'        => $this->image( 'addons@2x.min.png' ),
							'icon_alt'    => esc_html__( 'Add-ons', 'give' ),
							'title'       => esc_html__( 'GiveWP Add-ons', 'give' ),
							'description' => esc_html__( 'Make your fundraising even more effective with powerful features like Recurring Donations, ask donor\'s to cover processing fees, multiple currencies, eCard dedications, and much more. View our growing library of 35+ add-ons and extend your fundraising now.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/addons/' ),
									'screenReaderText' => __( 'View Add-ons for GiveWP', 'give' ),
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
				'title'    => __( 'Get the most out of GiveWP', 'give' ),
				'contents' => [
					$this->render_template(
						'row-item',
						[
							'icon'        => $this->image( 'givewp101@2x.min.png' ),
							'icon_alt'    => esc_html__( 'GiveWP Getting Started Guide', 'give' ),
							'title'       => esc_html__( 'GiveWP Getting Started Guide', 'give' ),
							'description' => esc_html__( 'Start off on the right foot by learning the basics of the plugin and how to get the most out of it to further your online fundraising efforts.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'target'           => '_blank',
									'href'             => $this->give_link( 'https://givewp.com/documentation/' ),
									'screenReaderText' => __( 'Learn more about GiveWP', 'give' ),
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
			'action' => admin_url( 'admin-post.php' ),
			'nonce'  => wp_nonce_field( 'dismiss_setup_page', $name = '_wpnonce', $referer = true, $echo = false ),
			'label'  => esc_html__( 'Dismiss Setup Screen', 'give' ),
		]
	)
	?>

</div>
