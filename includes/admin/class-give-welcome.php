<?php
/**
 * Give Welcome Page Class
 *
 * Displays on plugin activation
 *
 * @package     Give
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2019, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Welcome Class
 *
 * A general class for Welcome and Credits pages.
 *
 * @since 1.0
 */
class Give_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome' ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function admin_menus() {
		list( $display_version ) = explode( '-', GIVE_VERSION );

		// Changelog Page
		add_dashboard_page(
			esc_html__( 'What\'s New', 'give' ),
			esc_html__( 'What\'s New', 'give' ),
			$this->minimum_capability,
			'give-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			/* translators: %s: Give version */
			sprintf( esc_html__( 'GiveWP %s - Getting Started Guide', 'give' ), $display_version ),
			esc_html__( 'Getting started with Give', 'give' ),
			$this->minimum_capability,
			'give-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			/* translators: %s: Give version */
			sprintf( esc_html__( 'GiveWP %s - Credits', 'give' ), $display_version ),
			esc_html__( 'The people that build Give', 'give' ),
			$this->minimum_capability,
			'give-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function admin_head() {

		remove_submenu_page( 'index.php', 'give-changelog' );
		remove_submenu_page( 'index.php', 'give-getting-started' );
		remove_submenu_page( 'index.php', 'give-credits' );

	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'give-getting-started';
		?>
		<div class="nav-tab-wrapper give-nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'give-getting-started' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-getting-started' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Getting Started', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-changelog' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-changelog' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'What\'s New', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-add-ons' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-addons' ) ); ?>">
				<?php esc_html_e( 'Add-ons', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-credits' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-credits' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Credits', 'give' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * The header section for the welcome screen.
	 *
	 * @since 1.8.8
	 */
	public function get_welcome_header() {
		// Badge for welcome page
		list( $display_version ) = explode( '-', GIVE_VERSION );

		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		if ( empty( $page ) ) {
			return;
		}

		switch ( $page ) {
			case 'give-getting-started':
				$title   = sprintf( __( 'Welcome to GiveWP %s', 'give' ), $display_version );
				$content = __( 'Thank you for activating the latest version of Give! Welcome to the best fundraising platform for WordPress. We encourage you to check out the plugin documentation and getting started guide below.', 'give' );
				break;

			case 'give-changelog':
				$title   = sprintf( __( 'What\'s New in GiveWP %s', 'give' ), $display_version );
				$content = __( 'GiveWP is regularly updated with new features and fixes to ensure your fundraising campaigns run smoothly and securely. We always recommend keeping GiveWP up to date with the latest version.', 'give' );
				break;

			case 'give-credits':
				$title   = sprintf( __( 'GitHub Contributors', 'give' ) );
				$content = sprintf(
					/* translators: %s: https://github.com/impress-org/give */
					__( 'GiveWP is backed by a dedicated team of in-house developers and a vibrant open source community. If you are interested in contributing please visit the <a href="%s" target="_blank">GitHub Repo</a>.', 'give' ),
					esc_url( 'https://github.com/impress-org/give' )
				);

				break;

			default:
				$title   = get_admin_page_title();
				$content = '';
				break;

		}

		?>
		<div class="give-welcome-header">

			<div class="give-welcome-header-inner">

				<h1 class="give-welcome-h1"><?php esc_html_e( $title ); ?></h1>

				<?php $this->social_media_elements(); ?>

				<p class="give-welcome-text"><?php _e( $content ); ?></p>

				<?php $this->get_newsletter(); ?>

				<div class="give-badge">
					<?php
					printf(
						/* translators: %s: Give version */
						esc_html__( 'Version %s', 'give' ),
						$display_version
					);
					?>
				</div>

			</div>
		</div>

		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function getting_started_screen() {
		?>
		<div class="give-welcome-wrap get-started">

			<?php $this->get_welcome_header(); ?>

			<?php $this->tabs(); ?>

			<div class="give-welcome-content-wrap">

				<p class="give-welcome-content-intro"><?php esc_html_e( 'Getting started with GiveWP is easy! We put together this quick start guide to help first time users of the plugin. Our goal is to get you up and running in no time. Let\'s begin!', 'give' ); ?></p>

				<div class="give-feature-section give-clearfix">
					<div class="give-feature-section__inner">
						<div class="give-feature-section-item">
							<div class="give-feature-section-item__container">
								<h3>
									<span class="give-feature-section-item-number">1</span>
									<?php esc_html_e( 'Configure your payment methods', 'give' ); ?>
								</h3>

								<p><?php esc_html_e( 'Before you can begin fundraising, first you need to set up your payment gateway. Payment gateways allow you to accept payment methods through your donation forms. GiveWP supports many of the top payment processors through our add-ons. Stripe and PayPal Standard are included for free in the core plugin. Please ensure your site is running securely with a valid SSL certificate before accepting online payments.', 'give' ); ?></p>

								<p><?php echo sprintf( __( 'Having Trouble? Our team is here to help if you need to ask any questions. If you need help setting up your payment gateway, contact our <a href="%s" target="_blank">support team</a>.', 'give' ), 'https://givewp.com/support/?utm_source=welcome-screen&utm_medium=getting-started' ); ?></p>

								<div class="give-welcome-connect-gateways">

									<ul class="give-feature-btns">
										<li>
											<?php echo give_stripe_connect_button(); ?>
										</li>
										<li>
											<?php echo give_paypal_connect_button(); ?>
										</li>
										<li style="display: block; margin: 20px 0 0;">
											<a href="https://givewp.com/addons/category/payment-gateways/?utm_source=welcome-screen&utm_medium=getting-started"
											   class="give-feature-btn-link"
											   target="_blank"
											   title="<?php esc_attr_e( 'View Premium Gateways', 'give' ); ?>"><?php esc_html_e( 'View Premium Gateways', 'give' ); ?></a>
										</li>
									</ul>

									<p class="give-welcome-gateway-notice give-field-description"><?php esc_html_e( 'Note: The free version of the Stripe payment gateway for GiveWP does not include Apple or Google Pay. In the core plugin, using the free version of Stripe includes an additional 2% fee for a one-time donation in addition to the standard Stripe processing fee. Stripe Premium (the Stripe Add-on for Give) does not include this additional fee. Using PayPal standard does not include any additional fees. However, the donor will be taken to PayPal’s website to process their donation before being redirected back to your site.', 'give' ); ?></p>

								</div>


							</div>
						</div>

						<div class="give-feature-section-item">
							<div class="give-ipad-showcase-wrap">
								<div class="give-ipad-showcase-inner">
									<img
										src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-step-1.gif">
								</div>
							</div>
						</div>

					</div>
					<!-- /.give-feature-section__inner -->
				</div>
				<!-- /.give-feature-section -->

				<div class="give-feature-section give-feature-section__step2 give-clearfix">
					<div class="give-feature-section__inner">
						<div class="give-feature-section-item">
							<div class=" give-ipad-showcase-wrap">
								<div class="give-ipad-showcase-inner">
									<img
										src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-step-2.gif">
								</div>
							</div>
						</div>

						<div class="give-feature-section-item">
							<div
								class="give-feature-section-item__container give-feature-section-item__container-right">
								<h3>
									<span class="give-feature-section-item-number">2</span>
									<?php esc_html_e( 'Create your first donation form', 'give' ); ?>
								</h3>

								<p><?php esc_html_e( 'Donations are accepted through customizable forms. Forms can be stand-alone pages or embedded throughout your website using a block, shortcode, or widget. You can create multi-level forms which allow donors to choose from preconfigured donation amount, allow for custom amounts, and even set a fundraising goal. Customizing your forms with content and images is a breeze. You can also allow donors to leave comments, embed the form throughout your site and more.', 'give' ); ?></p>

								<ul class="give-feature-btns">
									<li>
										<a href="<?php echo admin_url( 'post-new.php?post_type=give_forms' ); ?>"
										   class="button button-primary button-large"
										   title="<?php esc_attr_e( 'Add new donation form', 'give' ); ?>"><?php esc_html_e( 'Add Donation Form', 'give' ); ?></a>
									</li>
									<li>
										<a href="http://docs.givewp.com/give-forms" class="give-feature-btn-link"
										   target="_blank"
										   title="<?php esc_attr_e( 'Learn more about Test Mode', 'give' ); ?>"><?php esc_html_e( 'Learn more', 'give' ); ?></a>
									</li>
								</ul>

							</div>
						</div>

					</div>
					<!-- /.give-feature-section__inner -->
				</div>
				<!-- /.give-feature-section -->

				<div class="give-feature-section give-clearfix">
					<div class="give-feature-section__inner">

						<div class="give-feature-section-item">
							<div class="give-feature-section-item__container">
								<h3>
									<span class="give-feature-section-item-number">3</span>
									<?php esc_html_e( 'Test and launch your campaign!', 'give' ); ?>
								</h3>

								<p><?php esc_html_e( 'You can choose these different modes by going to the "Form Content" section. From there, you can choose to add content before or after the donation form on a page, or if you choose "None" perhaps you want to instead use the shortcode. You can find the shortcode in the top right column directly under the Publish/Save button. This feature gives you the most amount of flexibility with controlling your content on your website all within the same page.', 'give' ); ?></p>

								<ul class="give-feature-btns">
									<li>
										<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ); ?>"
										   class="button button-primary button-large"
										   title="<?php esc_attr_e( 'Configure Test Mode', 'give' ); ?>"><?php esc_html_e( 'Configure Test Mode', 'give' ); ?></a>
									</li>
									<li>
										<a href="http://docs.givewp.com/test-mode" class="give-feature-btn-link"
										   target="_blank"
										   title="<?php esc_attr_e( 'Learn more about Test Mode', 'give' ); ?>"><?php esc_html_e( 'Learn more', 'give' ); ?></a>
									</li>
								</ul>

							</div>
						</div>

						<div class="give-feature-section-item">
							<div class="give-ipad-showcase-wrap">
								<div class="give-ipad-showcase-inner">
									<img
										src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-step-3.gif">
								</div>
							</div>
						</div>

					</div>
					<!-- /.give-feature-section__inner -->
				</div>
				<!-- /.give-feature-section -->

			</div>
			<!-- /.give-welcome-content-wrap -->

			<?php $this->support_widgets(); ?>

		</div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function changelog_screen() {
		?>
		<div class="give-welcome-wrap">

			<?php $this->get_welcome_header(); ?>

			<?php $this->tabs(); ?>

			<div class="give-welcome-content-wrap give-changelog-wrap">

				<p class="give-welcome-content-intro"><?php printf( __( 'See what\'s new in version %1$s of Give! If you feel we\'ve missed a fix or there\'s a feature you\'d like to see developed please <a href="%2$s" target="_blank">contact support</a>.', 'give' ), GIVE_VERSION, 'https://givewp.com/support/?utm_source=welcome-screen&utm_medium=getting-started' ); ?></p>

				<div class="give-changelog">
					<?php echo $this->parse_readme(); ?>
				</div>

			</div>

			<?php $this->support_widgets(); ?>

		</div>
		<?php
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function credits_screen() {
		?>
		<div class="wrap give-welcome-wrap">

			<?php $this->get_welcome_header(); ?>

			<?php $this->tabs(); ?>

			<div class="give-welcome-content-wrap give-changelog-wrap">

				<p class="give-welcome-content-intro">

					<?php
					printf(
						/* translators: %s: https://github.com/impress-org/give */
						__( 'GiveWP is backed by a dedicated team of in-house developers and a vibrant open source community. If you are interested in contributing please visit the <a href="%s" target="_blank">GitHub Repo</a>.', 'give' ),
						esc_url( 'https://github.com/impress-org/give' )
					);
					?>
				</p>

				<?php echo $this->contributors(); ?>

			</div>

		</div>
		<?php
	}


	/**
	 * Parse the GIVE readme.txt file
	 *
	 * @return string $readme HTML formatted readme file
	 * @since 1.0
	 */
	public function parse_readme() {
		$file = file_exists( GIVE_PLUGIN_DIR . 'readme.txt' ) ? GIVE_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . esc_html__( 'No valid changlog was found.', 'give' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	}


	/**
	 * Render Contributors List
	 *
	 * @return string $contributor_list HTML formatted list of all the contributors for GIVE
	 * @uses  Give_Welcome::get_contributors()
	 * @since 1.0
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) ) {
			return '';
		}

		$contributor_list = '<ul class="give-contributor-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="give-contributor">';
			$contributor_list .= sprintf(
				'<a href="%1$s" target="_blank"><img src="%2$s" width="64" height="64" class="gravatar" alt="%3$s" /><span>%3$s</span></a>',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_url( $contributor->avatar_url ),
				esc_attr( $contributor->login )
			);
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retrieve list of contributors from GitHub.
	 *
	 * @access public
	 * @return array $contributors List of contributors
	 * @since  1.0
	 */
	public function get_contributors() {
		$contributors = Give_Cache::get( 'give_contributors', true );

		if ( false !== $contributors ) {
			return $contributors;
		}

		$response = wp_remote_get( 'https://api.github.com/repos/impress-org/give/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) ) {
			return array();
		}

		Give_Cache::set( 'give_contributors', $contributors, HOUR_IN_SECONDS, true );

		return $contributors;
	}

	/**
	 * Social Media Like Buttons
	 *
	 * Various social media elements to Give
	 */
	public function social_media_elements() {
		?>

		<div class="social-items-wrap">

			<iframe
				src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwpgive&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=220596284639969"
				scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;"
				allowTransparency="true"></iframe>

			<a href="https://twitter.com/givewp" class="twitter-follow-button" data-show-count="false">
				<?php
				printf(
					/* translators: %s: Give twitter user @givewp */
					esc_html_e( 'Follow %s', 'give' ),
					'@givewp'
				);
				?>
			</a>
			<script>!function( d, s, id ) {
					var js, fjs = d.getElementsByTagName( s )[ 0 ], p = /^http:/.test( d.location ) ? 'http' : 'https';
					if ( !d.getElementById( id ) ) {
						js = d.createElement( s );
						js.id = id;
						js.src = p + '://platform.twitter.com/widgets.js';
						fjs.parentNode.insertBefore( js, fjs );
					}
				}( document, 'script', 'twitter-wjs' );
			</script>

		</div>
		<!--/.social-items-wrap -->

		<?php
	}

	/**
	 * Support widgets.
	 *
	 * @since 2.5.0
	 */
	public function support_widgets() {
	?>

			<div class="give-welcome-widgets give-clearfix">
			<div class="give-welcome-widgets__inner">

				<div class="give-welcome-widgets__heading">
					<h2><?php esc_html_e( 'Start off on the right foot', 'give' ); ?></h2>
					<p><?php esc_html_e( 'If you aren’t quite sure how to get started or you want to see the best ways to use GiveWP for your fundraising needs, book a demo. Our Customer Success Team is happy to help.', 'give' ); ?></p>

					<a href="https://givewp.com/schedule-a-demo/?utm_source=welcome-screen&utm_medium=getting-started"
					   class="give-welcome-widgets__demo-btn button button-large"
					   target="_blank"><?php esc_html_e( 'Schedule a Demo', 'give' ); ?></a>
				</div>

				<div class="give-welcome-widgets__col give-welcome-widgets__support">
					<div class="give-welcome-widgets__col-inner">
						<h3><?php esc_html_e( 'Support', 'give' ); ?></h3>
						<p><?php esc_html_e( 'Inevitably questions arise when building great fundraising websites. That’s exactly why we have a dedicated support staff of GiveWP experts to help you succeed with your campaign. ', 'give' ); ?></p>

						<a href="https://givewp.com/support/?utm_source=welcome-screen&utm_medium=getting-started" class="give-welcome-widgets__link"
						   target="_blank"><?php esc_html_e( 'How support works', 'give' ); ?></a>

					</div>
				</div>
				<div class="give-welcome-widgets__col give-welcome-widgets__addons">
					<div class="give-welcome-widgets__col-inner">
						<h3><?php esc_html_e( 'Add-ons', 'give' ); ?></h3>
						<p><?php esc_html_e( 'Accept recurring donations, add custom donation form fields, ask donors to cover processing fees and more! Level up your fundraisers by extending GiveWP with add-ons.', 'give' ); ?></p>
						<a href="https://givewp.com/addons/?utm_source=welcome-screen&utm_medium=getting-started" class="give-welcome-widgets__link"
						   target="_blank"><?php esc_html_e( 'Power up my fundraising', 'give' ); ?></a>
					</div>
				</div>
				<div class="give-welcome-widgets__col give-welcome-widgets__documentation">
					<div class="give-welcome-widgets__col-inner">
						<h3><?php esc_html_e( 'Documentation', 'give' ); ?></h3>
						<p><?php esc_html_e( 'Learn the ins and outs of GiveWP with well organized and clearly written documentation. You can search using a keyword to find articles for GiveWP Core and each add-on. ', 'give' ); ?></p>
						<a href="https://givewp.com/documentation/?utm_source=welcome-screen&utm_medium=getting-started" class="give-welcome-widgets__link"
						   target="_blank"><?php esc_html_e( 'Check out the docs', 'give' ); ?></a>
					</div>
				</div>

			</div>
			</div>
	<?php
	}

	/**
	 * Sends user to the Welcome page on first activation of Give.
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function welcome() {

		// Bail if no activation redirect
		if ( ! Give_Cache::get( '_give_activation_redirect', true ) || wp_doing_ajax() ) {
			return;
		}

		// Delete the redirect transient
		Give_Cache::delete( Give_Cache::get_key( '_give_activation_redirect' ) );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		$upgrade = get_option( 'give_version_upgraded_from' );

		if ( ! $upgrade ) {
			// First time install
			wp_safe_redirect( admin_url( 'index.php?page=give-getting-started' ) );
			exit;
		} elseif ( ! give_is_setting_enabled( give_get_option( 'welcome' ) ) ) {
			// Welcome is disabled in settings
		} else { // Welcome is NOT disabled in settings
			wp_safe_redirect( admin_url( 'index.php?page=give-changelog' ) );
			exit;
		}
	}

	/**
	 * Give Newsletter
	 *
	 * Returns the main Give newsletter form
	 */
	public function get_newsletter() {
		$current_user = wp_get_current_user();
		?>
		<div class="give-newsletter-form-wrap">

			<p class="give-newsletter-intro"><?php esc_html_e( 'Sign up for the below to stay informed about important updates, release notes, fundraising tips, and more! We\'ll never spam you.', 'give' ); ?></p>

			<form action="//givewp.us3.list-manage.com/subscribe/post?u=3ccb75d68bda4381e2f45794c&amp;id=12a081aa13"
				  method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form"
				  class="give-newsletter-form validate"
				  target="_blank">
				<div class="give-newsletter-confirmation">
					<p><?php esc_html_e( 'To complete your subscription, click the confirmation link in your email. Thank you!', 'give' ); ?></p>
				</div>

				<table class="form-table give-newsletter-form">
					<tr valign="middle">
						<td>
							<label for="mce-EMAIL"
								   class="screen-reader-text"><?php esc_html_e( 'Email Address (required)', 'give' ); ?></label>
							<input type="email" name="EMAIL" id="mce-EMAIL"
								   placeholder="<?php esc_attr_e( 'Email Address (required)', 'give' ); ?>"
								   class="required email" value="<?php echo $current_user->user_email; ?>" required>
						</td>
						<td>
							<label for="mce-FNAME"
								   class="screen-reader-text"><?php esc_html_e( 'First Name', 'give' ); ?></label>
							<input type="text" name="FNAME" id="mce-FNAME"
								   placeholder="<?php esc_attr_e( 'First Name', 'give' ); ?>" class=""
								   value="<?php echo $current_user->user_firstname; ?>" required>
						</td>
						<td>
							<label for="mce-LNAME"
								   class="screen-reader-text"><?php esc_html_e( 'Last Name', 'give' ); ?></label>
							<input type="text" name="LNAME" id="mce-LNAME"
								   placeholder="<?php esc_attr_e( 'Last Name', 'give' ); ?>" class=""
								   value="<?php echo $current_user->user_lastname; ?>">
						</td>
						<td>
							<input type="submit" name="subscribe" id="mc-embedded-subscribe"
								   class="button button-primary"
								   value="<?php esc_attr_e( 'Subscribe', 'give' ); ?>">
						</td>
					</tr>
				</table>
			</form>

			<div style="position: absolute; left: -5000px;">
				<input type="text" name="b_3ccb75d68bda4381e2f45794c_12a081aa13" tabindex="-1" value="">
			</div>

		</div>

		<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>
		<script type='text/javascript'>(
				function( $ ) {
					window.fnames = new Array();
					window.ftypes = new Array();
					fnames[ 0 ] = 'EMAIL';
					ftypes[ 0 ] = 'email';
					fnames[ 1 ] = 'FNAME';
					ftypes[ 1 ] = 'text';
					fnames[ 2 ] = 'LNAME';
					ftypes[ 2 ] = 'text';

					$( 'form[name="mc-embedded-subscribe-form"]' ).removeAttr( 'novalidate' );

					//Successful submission
					$( 'form[name="mc-embedded-subscribe-form"]' ).on( 'submit', function() {

						var email_field = $( this ).find( '#mce-EMAIL' ).val();
						if ( !email_field ) {
							return false;
						}
						$( this ).find( '.give-newsletter-confirmation' ).show();
						$( this ).find( '.give-newsletter-form' ).hide();

					} );

				}( jQuery )
			);
			var $mcj = jQuery.noConflict( true );


		</script>
		<!--End mc_embed_signup-->

		<?php
	}

}

new Give_Welcome();
