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

			<form method="POST" action="https://givewp.activehosted.com/proc.php" id="_form_3_" class="_form _form_3 _inline-form  _dark" novalidate>
				<input type="hidden" name="u" value="3"/>
				<input type="hidden" name="f" value="3"/>
				<input type="hidden" name="s"/>
				<input type="hidden" name="c" value="0"/>
				<input type="hidden" name="m" value="0"/>
				<input type="hidden" name="act" value="sub"/>
				<input type="hidden" name="v" value="2"/>
				<div class="_form-content">
					<div class="_form_element _x26983741 give-welcome-newsletter-fname">
						<label class="_form-label" style="display: none;">
							First Name
						</label>
						<div class="_field-wrapper">
							<input type="text" name="firstname" placeholder="Type your first name" value="<?php echo $current_user->user_firstname; ?>"/>
						</div>
					</div>
					<div class="_form_element _x63244763 give-welcome-newsletter-email">
						<label class="_form-label" style="display: none;">
							Email*
						</label>
						<div class="_field-wrapper">
							<input type="text" name="email" placeholder="Type your email" value="<?php echo $current_user->user_email; ?>" required/>
						</div>
					</div>
					<div class="_form_element _field1 _full_width give-welcome-newsletter-siteurl" style="display: none;">
						<label class="_form-label" style="display: none;">
							Organization Website
						</label>
						<div class="_field-wrapper">
							<input type="text" name="field[1]" value="<?php echo get_home_url(); ?>" placeholder=""/>
						</div>
					</div>
					<div class="_button-wrapper _full_width">
						<button id="_form_3_submit" class="_submit button button-primary" type="submit">
							Submit
						</button>
					</div>
				</div>
				<div class="_form-thank-you" style="display:none;">
				</div>

			</form>
			<script type="text/javascript">
				window.cfields = { "1": "organization_website" };
				window._show_thank_you = function( id, message, trackcmp_url ) {
					var form = document.getElementById( '_form_' + id + '_' ),
						thank_you = form.querySelector( '._form-thank-you' );
					form.querySelector( '._form-content' ).style.display = 'none';
					thank_you.innerHTML = message;
					thank_you.style.display = 'block';
					if ( typeof ( trackcmp_url ) != 'undefined' && trackcmp_url ) {
						// Site tracking URL to use after inline form submission.
						_load_script( trackcmp_url );
					}
					if ( typeof window._form_callback !== 'undefined' ) window._form_callback( id );
				};
				window._show_error = function( id, message, html ) {
					var form = document.getElementById( '_form_' + id + '_' ), err = document.createElement( 'div' ),
						button = form.querySelector( 'button' ), old_error = form.querySelector( '._form_error' );
					if ( old_error ) old_error.parentNode.removeChild( old_error );
					err.innerHTML = message;
					err.className = '_error-inner _form_error _no_arrow';
					var wrapper = document.createElement( 'div' );
					wrapper.className = '_form-inner';
					wrapper.appendChild( err );
					button.parentNode.insertBefore( wrapper, button );
					document.querySelector( '[id^="_form"][id$="_submit"]' ).disabled = false;
					if ( html ) {
						var div = document.createElement( 'div' );
						div.className = '_error-html';
						div.innerHTML = html;
						err.appendChild( div );
					}
				};
				window._load_script = function( url, callback ) {
					var head = document.querySelector( 'head' ), script = document.createElement( 'script' ), r = false;
					script.type = 'text/javascript';
					script.charset = 'utf-8';
					script.src = url;
					if ( callback ) {
						script.onload = script.onreadystatechange = function() {
							if ( !r && ( !this.readyState || this.readyState == 'complete' ) ) {
								r = true;
								callback();
							}
						};
					}
					head.appendChild( script );
				};
				( function() {
					if ( window.location.search.search( "excludeform" ) !== - 1 ) return false;
					var getCookie = function( name ) {
						var match = document.cookie.match( new RegExp( '(^|; )' + name + '=([^;]+)' ) );
						return match ? match[ 2 ] : null;
					}
					var setCookie = function( name, value ) {
						var now = new Date();
						var time = now.getTime();
						var expireTime = time + 1000 * 60 * 60 * 24 * 365;
						now.setTime( expireTime );
						document.cookie = name + '=' + value + '; expires=' + now + ';path=/';
					}
					var addEvent = function( element, event, func ) {
						if ( element.addEventListener ) {
							element.addEventListener( event, func );
						} else {
							var oldFunc = element[ 'on' + event ];
							element[ 'on' + event ] = function() {
								oldFunc.apply( this, arguments );
								func.apply( this, arguments );
							};
						}
					}
					var _removed = false;
					var form_to_submit = document.getElementById( '_form_3_' );
					var allInputs = form_to_submit.querySelectorAll( 'input, select, textarea' ), tooltips = [],
						submitted = false;

					var getUrlParam = function( name ) {
						var regexStr = '[\?&]' + name + '=([^&#]*)';
						var results = new RegExp( regexStr, 'i' ).exec( window.location.href );
						return results != undefined ? decodeURIComponent( results[ 1 ] ) : false;
					};

					for ( var i = 0; i < allInputs.length; i ++ ) {
						var regexStr = "field\\[(\\d+)\\]";
						var results = new RegExp( regexStr ).exec( allInputs[ i ].name );
						if ( results != undefined ) {
							allInputs[ i ].dataset.name = window.cfields[ results[ 1 ] ];
						} else {
							allInputs[ i ].dataset.name = allInputs[ i ].name;
						}
						var fieldVal = getUrlParam( allInputs[ i ].dataset.name );

						if ( fieldVal ) {
							if ( allInputs[ i ].dataset.autofill === "false" ) {
								continue;
							}
							if ( allInputs[ i ].type == "radio" || allInputs[ i ].type == "checkbox" ) {
								if ( allInputs[ i ].value == fieldVal ) {
									allInputs[ i ].checked = true;
								}
							} else {
								allInputs[ i ].value = fieldVal;
							}
						}
					}

					var remove_tooltips = function() {
						for ( var i = 0; i < tooltips.length; i ++ ) {
							tooltips[ i ].tip.parentNode.removeChild( tooltips[ i ].tip );
						}
						tooltips = [];
					};
					var remove_tooltip = function( elem ) {
						for ( var i = 0; i < tooltips.length; i ++ ) {
							if ( tooltips[ i ].elem === elem ) {
								tooltips[ i ].tip.parentNode.removeChild( tooltips[ i ].tip );
								tooltips.splice( i, 1 );
								return;
							}
						}
					};
					var create_tooltip = function( elem, text ) {
						var tooltip = document.createElement( 'div' ), arrow = document.createElement( 'div' ),
							inner = document.createElement( 'div' ), new_tooltip = {};
						if ( elem.type != 'radio' && elem.type != 'checkbox' ) {
							tooltip.className = '_error';
							arrow.className = '_error-arrow';
							inner.className = '_error-inner';
							inner.innerHTML = text;
							tooltip.appendChild( arrow );
							tooltip.appendChild( inner );
							elem.parentNode.appendChild( tooltip );
						} else {
							tooltip.className = '_error-inner _no_arrow';
							tooltip.innerHTML = text;
							elem.parentNode.insertBefore( tooltip, elem );
							new_tooltip.no_arrow = true;
						}
						new_tooltip.tip = tooltip;
						new_tooltip.elem = elem;
						tooltips.push( new_tooltip );
						return new_tooltip;
					};
					var resize_tooltip = function( tooltip ) {
						var rect = tooltip.elem.getBoundingClientRect();
						var doc = document.documentElement,
							scrollPosition = rect.top - ( ( window.pageYOffset || doc.scrollTop ) - ( doc.clientTop || 0 ) );
						if ( scrollPosition < 40 ) {
							tooltip.tip.className = tooltip.tip.className.replace( / ?(_above|_below) ?/g, '' ) + ' _below';
						} else {
							tooltip.tip.className = tooltip.tip.className.replace( / ?(_above|_below) ?/g, '' ) + ' _above';
						}
					};
					var resize_tooltips = function() {
						if ( _removed ) return;
						for ( var i = 0; i < tooltips.length; i ++ ) {
							if ( !tooltips[ i ].no_arrow ) resize_tooltip( tooltips[ i ] );
						}
					};
					var validate_field = function( elem, remove ) {
						var tooltip = null, value = elem.value, no_error = true;
						remove ? remove_tooltip( elem ) : false;
						if ( elem.type != 'checkbox' ) elem.className = elem.className.replace( / ?_has_error ?/g, '' );
						if ( elem.getAttribute( 'required' ) !== null ) {
							if ( elem.type == 'radio' || ( elem.type == 'checkbox' && /any/.test( elem.className ) ) ) {
								var elems = form_to_submit.elements[ elem.name ];
								if ( !( elems instanceof NodeList || elems instanceof HTMLCollection ) || elems.length <= 1 ) {
									no_error = elem.checked;
								} else {
									no_error = false;
									for ( var i = 0; i < elems.length; i ++ ) {
										if ( elems[ i ].checked ) no_error = true;
									}
								}
								if ( !no_error ) {
									tooltip = create_tooltip( elem, "Please select an option." );
								}
							} else if ( elem.type == 'checkbox' ) {
								var elems = form_to_submit.elements[ elem.name ], found = false, err = [];
								no_error = true;
								for ( var i = 0; i < elems.length; i ++ ) {
									if ( elems[ i ].getAttribute( 'required' ) === null ) continue;
									if ( !found && elems[ i ] !== elem ) return true;
									found = true;
									elems[ i ].className = elems[ i ].className.replace( / ?_has_error ?/g, '' );
									if ( !elems[ i ].checked ) {
										no_error = false;
										elems[ i ].className = elems[ i ].className + ' _has_error';
										err.push( "Checking %s is required".replace( "%s", elems[ i ].value ) );
									}
								}
								if ( !no_error ) {
									tooltip = create_tooltip( elem, err.join( '<br/>' ) );
								}
							} else if ( elem.tagName == 'SELECT' ) {
								var selected = true;
								if ( elem.multiple ) {
									selected = false;
									for ( var i = 0; i < elem.options.length; i ++ ) {
										if ( elem.options[ i ].selected ) {
											selected = true;
											break;
										}
									}
								} else {
									for ( var i = 0; i < elem.options.length; i ++ ) {
										if ( elem.options[ i ].selected && !elem.options[ i ].value ) {
											selected = false;
										}
									}
								}
								if ( !selected ) {
									elem.className = elem.className + ' _has_error';
									no_error = false;
									tooltip = create_tooltip( elem, "Please select an option." );
								}
							} else if ( value === undefined || value === null || value === '' ) {
								elem.className = elem.className + ' _has_error';
								no_error = false;
								tooltip = create_tooltip( elem, "This field is required." );
							}
						}
						if ( no_error && elem.name == 'email' ) {
							if ( !value.match( /^[\+_a-z0-9-'&=]+(\.[\+_a-z0-9-']+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i ) ) {
								elem.className = elem.className + ' _has_error';
								no_error = false;
								tooltip = create_tooltip( elem, "Enter a valid email address." );
							}
						}
						if ( no_error && /date_field/.test( elem.className ) ) {
							if ( !value.match( /^\d\d\d\d-\d\d-\d\d$/ ) ) {
								elem.className = elem.className + ' _has_error';
								no_error = false;
								tooltip = create_tooltip( elem, "Enter a valid date." );
							}
						}
						tooltip ? resize_tooltip( tooltip ) : false;
						return no_error;
					};
					var needs_validate = function( el ) {
						return el.name == 'email' || el.getAttribute( 'required' ) !== null;
					};
					var validate_form = function( e ) {
						var err = form_to_submit.querySelector( '._form_error' ), no_error = true;
						if ( !submitted ) {
							submitted = true;
							for ( var i = 0, len = allInputs.length; i < len; i ++ ) {
								var input = allInputs[ i ];
								if ( needs_validate( input ) ) {
									if ( input.type == 'text' ) {
										addEvent( input, 'blur', function() {
											this.value = this.value.trim();
											validate_field( this, true );
										} );
										addEvent( input, 'input', function() {
											validate_field( this, true );
										} );
									} else if ( input.type == 'radio' || input.type == 'checkbox' ) {
										( function( el ) {
											var radios = form_to_submit.elements[ el.name ];
											for ( var i = 0; i < radios.length; i ++ ) {
												addEvent( radios[ i ], 'click', function() {
													validate_field( el, true );
												} );
											}
										} )( input );
									} else if ( input.tagName == 'SELECT' ) {
										addEvent( input, 'change', function() {
											validate_field( this, true );
										} );
									} else if ( input.type == 'textarea' ) {
										addEvent( input, 'input', function() {
											validate_field( this, true );
										} );
									}
								}
							}
						}
						remove_tooltips();
						for ( var i = 0, len = allInputs.length; i < len; i ++ ) {
							var elem = allInputs[ i ];
							if ( needs_validate( elem ) ) {
								if ( elem.tagName.toLowerCase() !== "select" ) {
									elem.value = elem.value.trim();
								}
								validate_field( elem ) ? true : no_error = false;
							}
						}
						if ( !no_error && e ) {
							e.preventDefault();
						}
						resize_tooltips();
						return no_error;
					};
					addEvent( window, 'resize', resize_tooltips );
					addEvent( window, 'scroll', resize_tooltips );
					window._old_serialize = null;
					if ( typeof serialize !== 'undefined' ) window._old_serialize = window.serialize;
					_load_script( "//d3rxaij56vjege.cloudfront.net/form-serialize/0.3/serialize.min.js", function() {
						window._form_serialize = window.serialize;
						if ( window._old_serialize ) window.serialize = window._old_serialize;
					} );
					var form_submit = function( e ) {
						e.preventDefault();
						if ( validate_form() ) {
							// use this trick to get the submit button & disable it using plain javascript
							document.querySelector( '#_form_3_submit' ).disabled = true;
							var serialized = _form_serialize( document.getElementById( '_form_3_' ) );
							var err = form_to_submit.querySelector( '._form_error' );
							err ? err.parentNode.removeChild( err ) : false;
							_load_script( 'https://givewp.activehosted.com/proc.php?' + serialized + '&jsonp=true' );
						}
						return false;
					};
					addEvent( form_to_submit, 'submit', form_submit );
				} )();

			</script>

		</div>


		<?php
	}

}

new Give_Welcome();
