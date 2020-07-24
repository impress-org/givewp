<div class="wrap" class="give-setup-page">

	<h1 class="wp-heading-inline">
		<?php echo __( 'Setup GiveWP', 'give' ); ?>
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
				'title'    => 'Create your first donation form in minutes',
				'badge'    => '<span class="badge badge-review">5-10 Minutes</span>',
				'contents' => $this->render_template(
					'row-item',
					[
						'class'       => 'configuration',
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
				'title'    => 'Connect a payment gateway to begin accepting donations',
				'contents' => [
					$this->render_template(
						'row-item',
						[
							'icon'        => $this->image( 'paypal@2x.min.png' ),
							'icon_alt'    => esc_html__( 'PayPal', 'give' ),
							'title'       => esc_html__( 'Connect to PayPal', 'give' ),
							'description' => esc_html__( 'PayPal is synonymous with nonprofits and online charitable gifts. It’s been the go-to payment merchant in for many of the worlds top NGOs. Accept PayPal, Credit and Debit Cards, and more using PayPal’s Smart Buttons without any added platform fees.', 'give' ),
							'action'      => '<img src="' . GIVE_PLUGIN_URL . 'assets/dist/images/setup-page/paypal.svg' . '" alt="Connect to PayPal" />',
						]
					),
					$this->render_template(
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
					),
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
				'title'    => 'Connect a payment gateway to begin accepting donations',
				'contents' => [
					$this->render_template(
						'row-item',
						[
							'icon'        => $this->image( 'givewp101@2x.min.png' ),
							'icon_alt'    => esc_html__( 'GiveWP 101', 'give' ),
							'title'       => esc_html__( 'GiveWP 101', 'give' ),
							'description' => esc_html__( 'Start off on the right foot by learning the basics of the plugin and how to get the most out of it to further your online fundraising efforts.', 'give' ),
							'action'      => $this->render_template(
								'action-link',
								[
									'href'             => '#',
									'screenReaderText' => 'Learn more about GiveWP',
								]
							),
						]
					),
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
									'href'             => '#',
									'screenReaderText' => 'View Add-ons for GiveWP',
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
