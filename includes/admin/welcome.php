<?php
/**
 * Give Welcome Page Class
 *
 * Displays on plugin activation
 * @package     Give
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2016, GiveWP
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
 * A general class for About and Credits page.
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
	 * @since  1.0
	 * @return void
	 */
	public function admin_menus() {
		list( $display_version ) = explode( '-', GIVE_VERSION );

		// About Page
		add_dashboard_page(
		/* translators: %s: Give version */
			sprintf( esc_html__( 'Welcome to GiveWP %s', 'give' ), $display_version ),
			esc_html__( 'Welcome to Give', 'give' ),
			$this->minimum_capability,
			'give-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			esc_html__( 'GiveWP Changelog', 'give' ),
			esc_html__( 'GiveWP Changelog', 'give' ),
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
	 * @since  1.0
	 * @return void
	 */
	public function admin_head() {

		remove_submenu_page( 'index.php', 'give-about' );
		remove_submenu_page( 'index.php', 'give-changelog' );
		remove_submenu_page( 'index.php', 'give-getting-started' );
		remove_submenu_page( 'index.php', 'give-credits' );

	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'give-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'give-about' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-about' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'About Give', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-getting-started' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-getting-started' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Getting Started', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-credits' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-credits' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Credits', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-add-ons' ? 'nav-tab-active' : ''; ?>"
			   href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-addons' ) ); ?>">
				<?php esc_html_e( 'Add-ons', 'give' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', GIVE_VERSION );
		?>
		<div class="wrap about-wrap">

			<?php $this->get_welcome_header() ?>

			<p class="about-text"><?php
				printf(
				/* translators: %s: http://docs.givewp.com/docs */
					__( 'Thank you for activating or updating to the latest version of Give! If you\'re a first time user, welcome! You\'re well on your way to empowering your cause. We encourage you to check out the <a href="%s" target="_blank">plugin documentation</a> and getting started guide below.', 'give' ),
					esc_url( 'http://docs.givewp.com/docs' )
				);
				?></p>

			<?php give_get_newsletter(); ?>

			<div class="give-badge"><?php
				printf(
				/* translators: %s: Give version */
					esc_html__( 'Version %s', 'give' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

			<div class="feature-section clearfix introduction">

				<div class="video feature-section-item">
					<img src="<?php echo GIVE_PLUGIN_URL . 'assets/dist/images/give-logo-photo-mashup.png' ?>"
						 alt="<?php esc_attr_e( 'Give', 'give' ); ?>">
				</div>

				<div class="content feature-section-item last-feature">

					<h3><?php esc_html_e( 'GiveWP - Democratizing Generosity', 'give' ); ?></h3>

					<p><?php esc_html_e( 'GiveWP empowers you to easily accept donations and setup fundraising campaigns, directly within WordPress. We created GiveWP to provide a better donation experience for you and your users. Robust, flexible, and intuitive, the plugin is built from the ground up to be the goto donation solution for WordPress. Create powerful donation forms, embed them throughout your website, start a campaign, and exceed your fundraising goals with Give. This plugin is actively developed and proudly supported by folks who are dedicated to helping you and your cause.', 'give' ); ?></p>
					<a href="https://givewp.com" target="_blank" class="button-secondary">
						<?php esc_html_e( 'Learn More', 'give' ); ?>
						<span class="dashicons dashicons-external"></span>
					</a>

				</div>

			</div>
			<!-- /.intro-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item">

					<h3><?php esc_html_e( 'Getting to Know Give', 'give' ); ?></h3>

					<p><?php esc_html_e( 'Before you get started with GiveWP we suggest you take a look at the online documentation. There you will find the getting started guide which will help you get up and running quickly. If you have a question, issue or bug with the Core plugin please submit an issue on the GiveWP website. We also welcome your feedback and feature requests. Welcome to Give. We hope you much success with your cause.', 'give' ); ?></p>

					<h4>Find Out More:</h4>
					<ul class="ul-disc">
						<li><a href="https://givewp.com/"
							   target="_blank"><?php esc_html_e( 'Visit the GiveWP Website', 'give' ); ?></a></li>
						<li><a href="https://givewp.com/features/"
							   target="_blank"><?php esc_html_e( 'View the GiveWP Features', 'give' ); ?></a></li>
						<li><a href="https://givewp.com/documentation/"
							   target="_blank"><?php esc_html_e( 'Read the Documentation', 'give' ); ?></a></li>
					</ul>

				</div>

				<div class="content  feature-section-item last-feature">
					<img src="<?php echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/give-form-mockup.png' ?>"
						 alt="<?php esc_attr_e( 'A GiveWP donation form', 'give' ); ?>">
				</div>

			</div>
			<!-- /.feature-section -->


		</div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function changelog_screen() {
		list( $display_version ) = explode( '-', GIVE_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php echo get_admin_page_title(); ?></h1>

			<p class="about-text"><?php
				printf(
				/* translators: %s: Give version */
					esc_html__( 'Thank you for updating to the latest version! GiveWP %s is ready to make your online store faster, safer, and better!', 'give' ),
					$display_version
				);
				?></p>
			<div class="give-badge"><?php
				printf(
				/* translators: %s: Give version */
					esc_html__( 'Version %s', 'give' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php esc_html_e( 'Full Changelog', 'give' ); ?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array(
					'post_type' => 'give_forms',
					'page'      => 'give-settings'
				), 'edit.php' ) ) ); ?>"><?php esc_html_e( 'GiveWP Settings', 'give' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', GIVE_VERSION );
		?>
		<div class="wrap about-wrap get-started">

			<?php $this->get_welcome_header() ?>

			<p class="about-text"><?php esc_html_e( 'Welcome to the getting started guide.', 'give' ); ?></p>

			<?php give_get_newsletter(); ?>

			<div class="give-badge"><?php
				printf(
				/* translators: %s: Give version */
					esc_html__( 'Version %s', 'give' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

			<p class="about-text"><?php printf( esc_html__( 'Getting started with GiveWP is easy! We put together this quick start guide to help first time users of the plugin. Our goal is to get you up and running in no time. Let\'s begin!', 'give' ), $display_version ); ?></p>

			<div class="feature-section clearfix">

				<div class="content feature-section-item">
					<h3><?php esc_html_e( 'STEP 1: Create a New Form', 'give' ); ?></h3>

					<p><?php esc_html_e( 'GiveWP is driven by its powerful donation form building features. However, it is much more than just a "donation form." From the "Add Form" page you\'ll be able to choose how and where you want to receive your donations. You will also be able to set the preferred donation amounts.', 'give' ); ?></p>

					<p><?php esc_html_e( 'All of these features begin by simply going to the menu and choosing "Donations > Add Form."', 'give' ); ?></p>
				</div>

				<div class="content feature-section-item last-feature">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-add-new-form.png">
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item multi-level-gif">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-new-form-multi-level.gif">
				</div>

				<div class="content feature-section-item last-feature">
					<h3><?php esc_html_e( 'STEP 2: Customize Your Donation Forms', 'give' ); ?></h3>

					<p><?php esc_html_e( 'Each donation form you create can be customized to receive either a pre-determined set donation amount or have multiple suggested levels of giving. Choosing "Multi-level Donation" opens up the donation levels view where you can add as many levels as you\'d like with your own custom names and suggested amounts. As well, you can allow donors to give a custom amount and even set up donation goals.', 'give' ); ?></p>
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item add-content">
					<h3><?php esc_html_e( 'STEP 3: Add Additional Content', 'give' ); ?></h3>

					<p><?php esc_html_e( 'Every donation form you create with GiveWP can be used on its own stand-alone page, or it can be inserted into any other page or post throughout your site via a shortcode or widget.', 'give' ); ?></p>

					<p><?php esc_html_e( 'You can choose these different modes by going to the "Form Content" section. From there, you can choose to add content before or after the donation form on a page, or if you choose "None" perhaps you want to instead use the shortcode. You can find the shortcode in the top right column directly under the Publish/Save button. This feature gives you the most amount of flexibility with controlling your content on your website all within the same page.', 'give' ); ?></p>
				</div>

				<div class="content feature-section-item last-feature">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-add-content.png">
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item display-options">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/dist/images/admin/getting-started-display-options.png">
				</div>

				<div class="content feature-section-item last-feature">
					<h3><?php esc_html_e( 'STEP 4: Configure Your Display Options', 'give' ); ?></h3>

					<p><?php esc_html_e( 'Lastly, you can present the form in a number of different ways that each create their own unique donor experience. The "Modal" display mode opens the credit card fieldset within a popup window. The "Reveal" mode will slide into place the additional fields. If you\'re looking for a simple button, then "Button" more is the way to go. This allows you to create a customizable "Donate Now" button which will open the donation form upon clicking. There\'s tons of possibilities here, give it a try!', 'give' ); ?></p>
				</div>


			</div>
			<!-- /.feature-section -->


		</div>
		<?php
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function credits_screen() {
		list( $display_version ) = explode( '-', GIVE_VERSION );
		?>
		<div class="wrap about-wrap">

			<?php $this->get_welcome_header() ?>

			<p class="about-text"><?php esc_html_e( 'Thanks to all those who have contributed code directly or indirectly.', 'give' ); ?></p>

			<?php give_get_newsletter(); ?>

			<div class="give-badge"><?php
				printf(
				/* translators: %s: Give version */
					esc_html__( 'Version %s', 'give' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php
				printf(
				/* translators: %s: https://github.com/impress-org/give */
					__( 'GiveWP is created by a dedicated team of developers. If you are interested in contributing please visit the <a href="%s" target="_blank">GitHub Repo</a>.', 'give' ),
					esc_url( 'https://github.com/impress-org/give' )
				);
				?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Parse the GIVE readme.txt file
	 *
	 * @since 1.0
	 * @return string $readme HTML formatted readme file
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

			$readme = give_get_format_md( $readme );
		}

		return $readme;
	}


	/**
	 * Render Contributors List
	 *
	 * @since 1.0
	 * @uses  Give_Welcome::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for GIVE
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) ) {
			return '';
		}

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf(
				'<a href="%1$s" target="_blank"><img src="%2$s" width="64" height="64" class="gravatar" alt="%3$s" /></a>',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_url( $contributor->avatar_url ),
				esc_attr( $contributor->login )
			);
			$contributor_list .= sprintf(
				'<a class="web" target="_blank" href="%1$s">%2$s</a>',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( $contributor->login )
			);
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @access public
	 * @since  1.0
	 * @return array $contributors List of contributors
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
	 * The header section for the welcome screen.
	 *
	 * @since 1.8.8
	 */
	public function get_welcome_header() {
		// Badge for welcome page
		$badge_url = GIVE_PLUGIN_URL . 'assets/dist/images/give-badge.png';
		?>
		<h1 class="welcome-h1"><?php echo get_admin_page_title(); ?></h1>
		<?php $this->social_media_elements(); ?>

		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.give-badge {
				background: url('<?php echo $badge_url; ?>') no-repeat;
			}

			/*]]>*/
		</style>
		<script>
			//FitVids
			(function (e) {
				"use strict";
				e.fn.fitVids = function (t) {
					var n = {customSelector: null, ignore: null};
					if (!document.getElementById("fit-vids-style")) {
						var r = document.head || document.getElementsByTagName("head")[0];
						var i = ".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}";
						var s = document.createElement("div");
						s.innerHTML = '<p>x</p><style id="fit-vids-style">' + i + "</style>";
						r.appendChild(s.childNodes[1])
					}
					if (t) {
						e.extend(n, t)
					}
					return this.each(function () {
						var t = ['iframe[src*="player.vimeo.com"]', 'iframe[src*="youtube.com"]', 'iframe[src*="youtube-nocookie.com"]', 'iframe[src*="kickstarter.com"][src*="video.html"]', "object", "embed"];
						if (n.customSelector) {
							t.push(n.customSelector)
						}
						var r = ".fitvidsignore";
						if (n.ignore) {
							r = r + ", " + n.ignore
						}
						var i = e(this).find(t.join(","));
						i = i.not("object object");
						i = i.not(r);
						i.each(function () {
							var t = e(this);
							if (t.parents(r).length > 0) {
								return
							}
							if (this.tagName.toLowerCase() === "embed" && t.parent("object").length || t.parent(".fluid-width-video-wrapper").length) {
								return
							}
							if (!t.css("height") && !t.css("width") && (isNaN(t.attr("height")) || isNaN(t.attr("width")))) {
								t.attr("height", 9);
								t.attr("width", 16)
							}
							var n = this.tagName.toLowerCase() === "object" || t.attr("height") && !isNaN(parseInt(t.attr("height"), 10)) ? parseInt(t.attr("height"), 10) : t.height(),
								i = !isNaN(parseInt(t.attr("width"), 10)) ? parseInt(t.attr("width"), 10) : t.width(),
								s = n / i;
							if (!t.attr("id")) {
								var o = "fitvid" + Math.floor(Math.random() * 999999);
								t.attr("id", o)
							}
							t.wrap('<div class="fluid-width-video-wrapper"></div>').parent(".fluid-width-video-wrapper").css("padding-top", s * 100 + "%");
							t.removeAttr("height").removeAttr("width")
						})
					})
				}
			})(window.jQuery || window.Zepto);
			jQuery(document).ready(function ($) {

				// Target your .container, .wrapper, .post, etc.
				$(".wrap").fitVids();

			});

		</script>
	<?php }


	/**
	 * Social Media Like Buttons
	 *
	 * Various social media elements to Give
	 */
	public function social_media_elements() { ?>

		<div class="social-items-wrap">

			<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwpgive&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=220596284639969"
					scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;"
					allowTransparency="true"></iframe>

			<a href="https://twitter.com/givewp" class="twitter-follow-button" data-show-count="false"><?php
				printf(
				/* translators: %s: Give twitter user @givewp */
					esc_html_e( 'Follow %s', 'give' ),
					'@givewp'
				);
				?></a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = p + '://platform.twitter.com/widgets.js';
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, 'script', 'twitter-wjs');
			</script>

		</div>
		<!--/.social-items-wrap -->

		<?php
	}


	/**
	 * Sends user to the Welcome page on first activation of GIVE as well as each
	 * time GIVE is upgraded to a new version
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
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

		if ( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		} elseif ( ! give_is_setting_enabled( give_get_option( 'welcome' ) ) ) { // Welcome is disabled in settings

		} else { // Welcome is NOT disabled in settings
			wp_safe_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		}
	}

}

new Give_Welcome();
