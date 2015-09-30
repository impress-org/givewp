<?php
/**
 * Give Welcome Page Class
 *
 * @description Displays on plugin activation
 * @package     Give
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
		// About Page
		add_dashboard_page(
			__( 'Welcome to Give', 'give' ),
			__( 'Welcome to Give', 'give' ),
			$this->minimum_capability,
			'give-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'Give Changelog', 'give' ),
			__( 'Give Changelog', 'give' ),
			$this->minimum_capability,
			'give-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Give', 'give' ),
			__( 'Getting started with Give', 'give' ),
			$this->minimum_capability,
			'give-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Give', 'give' ),
			__( 'The people that build Give', 'give' ),
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

		// Badge for welcome page
		$badge_url = GIVE_PLUGIN_URL . 'assets/images/give-badge.png';

		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.give-badge {
				padding-top: 150px;
				height: 52px;
				width: 185px;
				color: #FFF;
				font-weight: bold;
				font-size: 14px;
				text-align: center;
				text-shadow: 0 1px 0 rgba(0, 0, 0, 0.8);
				margin: 0 -5px;
				background: url('<?php echo $badge_url; ?>') no-repeat;
			}

			.about-wrap .give-badge {
				position: absolute;
				top: 0;
				right: 0;
			}

			.give-welcome-screenshots {
				float: right;
				margin-left: 10px !important;
			}

			.about-wrap .feature-section {
				margin-top: 40px;
			}

			.introduction {
				padding: 0 0 20px;
				margin: 0 0 20px;
			}

			.about-wrap h3 {
				margin: 1.5em 0 .6em;
			}

			/*]]>*/
		</style>
		<script>
			//FitVids
			(function ( e ) {
				"use strict";
				e.fn.fitVids = function ( t ) {
					var n = {customSelector: null, ignore: null};
					if ( !document.getElementById( "fit-vids-style" ) ) {
						var r = document.head || document.getElementsByTagName( "head" )[0];
						var i = ".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}";
						var s = document.createElement( "div" );
						s.innerHTML = '<p>x</p><style id="fit-vids-style">' + i + "</style>";
						r.appendChild( s.childNodes[1] )
					}
					if ( t ) {
						e.extend( n, t )
					}
					return this.each( function () {
						var t = ['iframe[src*="player.vimeo.com"]', 'iframe[src*="youtube.com"]', 'iframe[src*="youtube-nocookie.com"]', 'iframe[src*="kickstarter.com"][src*="video.html"]', "object", "embed"];
						if ( n.customSelector ) {
							t.push( n.customSelector )
						}
						var r = ".fitvidsignore";
						if ( n.ignore ) {
							r = r + ", " + n.ignore
						}
						var i = e( this ).find( t.join( "," ) );
						i = i.not( "object object" );
						i = i.not( r );
						i.each( function () {
							var t = e( this );
							if ( t.parents( r ).length > 0 ) {
								return
							}
							if ( this.tagName.toLowerCase() === "embed" && t.parent( "object" ).length || t.parent( ".fluid-width-video-wrapper" ).length ) {
								return
							}
							if ( !t.css( "height" ) && !t.css( "width" ) && (isNaN( t.attr( "height" ) ) || isNaN( t.attr( "width" ) )) ) {
								t.attr( "height", 9 );
								t.attr( "width", 16 )
							}
							var n = this.tagName.toLowerCase() === "object" || t.attr( "height" ) && !isNaN( parseInt( t.attr( "height" ), 10 ) ) ? parseInt( t.attr( "height" ), 10 ) : t.height(), i = !isNaN( parseInt( t.attr( "width" ), 10 ) ) ? parseInt( t.attr( "width" ), 10 ) : t.width(), s = n / i;
							if ( !t.attr( "id" ) ) {
								var o = "fitvid" + Math.floor( Math.random() * 999999 );
								t.attr( "id", o )
							}
							t.wrap( '<div class="fluid-width-video-wrapper"></div>' ).parent( ".fluid-width-video-wrapper" ).css( "padding-top", s * 100 + "%" );
							t.removeAttr( "height" ).removeAttr( "width" )
						} )
					} )
				}
			})( window.jQuery || window.Zepto );
			jQuery( document ).ready( function ( $ ) {

				// Target your .container, .wrapper, .post, etc.
				$( ".wrap" ).fitVids();

			} );

		</script>
	<?php
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
			<a class="nav-tab <?php echo $selected == 'give-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "About Give", 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-add-ons' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( null, 'index.php' ) ) . 'edit.php?post_type=give_forms&page=give-addons'; ?>">
				<?php _e( 'Add-ons', 'give' ); ?>
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
			<h1 class="welcome-h1"><?php printf( __( 'Welcome to Give %s', 'give' ), $display_version ); ?></h1>

			<?php give_social_media_elements() ?>

			<div class="about-text"><?php printf( __( 'Thank you for activating or updating to the latest version of Give! If you\'re a first time user, welcome! You\'re well on your way to empowering your cause. </You>We encourage you to check out the <a href="%s" title="View the Give plugin documentation online" target="_blank">plugin documentation</a> and getting started guide below.', 'give' ), 'https://givewp.com/documenation/' ); ?></div>

			<p class="newsletter-intro"><?php _e( 'Be sure to sign up for the Give newsletter below to stay informed of important updates and news.', 'give' ); ?></p>

			<?php give_get_newsletter() ?>

			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>



			<?php $this->tabs(); ?>

			<div class="feature-section clearfix introduction">

				<div class="video feature-section-item">
					<img src="<?php echo GIVE_PLUGIN_URL . '/assets/images/give-form-mockup.png' ?>" title="A Give donation form" alt="A Give donation form">

				</div>

				<div class="content feature-section-item last-feature">

					<h3><?php _e( 'Give - Democratizing Generosity', 'give' ); ?></h3>

					<p><?php _e( 'Give empowers you to easily accept donations and setup fundraising campaigns, directly within WordPress. We created Give to provide a better donation experience for you and your users. Robust, flexible, and intuitive, the plugin is built from the ground up to be the goto donation solution for WordPress. Create powerful donation forms, embed them throughout your website, start a campaign, and exceed your fundraising goals with Give. This plugin is actively developed and proudly supported by folks who are dedicated to helping you and your cause.', 'give' ); ?></p>
					<a href="https://givewp.com" target="_blank" class="button-secondary" title="Visit the Give Website">Learn More
						<span class="dashicons dashicons-external"></span></a>

				</div>

			</div>
			<!-- /.intro-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item">

					<h3><?php _e( 'Getting to Know Give', 'give' ); ?></h3>

					<p><?php _e( 'Before you get started with Give we suggest you take a look at the online documentation. There you will find the getting started guide which will help you get up and running quickly. If you have an question, issue or bug with the Core plugin please submit an issue on the Give website. We also welcome your feedback and feature requests. Welcome to Give. We hope you much success with your cause.', 'give' ); ?></p>
					<a href="https://givewp.com/documentation" target="_blank" class="button-secondary" title="Visit the Give Website">View Documentation
						<span class="dashicons dashicons-external"></span></a>

				</div>

				<div class="content  feature-section-item last-feature">

					<img src="<?php echo GIVE_PLUGIN_URL . '/assets/images/give-logo-photo-mashup.png' ?>" title="Give" alt="Give">

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
			<h1><?php _e( 'Give Changelog', 'give' ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Give %s is ready to make your online store faster, safer, and better!', 'give' ), $display_version ); ?></div>
			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'give' ); ?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array(
					'post_type' => 'give_forms',
					'page'      => 'give-settings'
				), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Give Settings', 'give' ); ?></a>
			</div>
		</div>
	<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since  1.9
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', GIVE_VERSION );
		?>
		<div class="wrap about-wrap get-started">
			<h1 class="welcome-h1"><?php printf( __( 'Give %s - Getting Started Guide', 'give' ), $display_version ); ?></h1>

			<?php give_social_media_elements() ?>

			<div class="about-text"><?php _e( 'Welcome to the getting started guide.' ); ?></div>

			<p class="newsletter-intro"><?php _e( 'Don\'t forget to sign up for the newsletter!', 'give' ); ?>.</p>

			<?php give_get_newsletter() ?>

			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>


			<div class="about-text"><?php printf( __( 'Getting started with Give is easy! We put together this quick start guide to help first time users of the plugin. Our goal is to get you up and running in no time. Let\'s begin!', 'give' ), $display_version ); ?></div>


			<div class="feature-section clearfix">

				<div class="content feature-section-item">
					<h3><?php _e( 'STEP 1: Create a New Form', 'give' ); ?></h3>

					<p><?php _e( 'Give is driven by it\'s powerful form building features. But it is not simply a "form". From the "Add New Form" page you\'ll be able to choose how and where you want to receive your donations. You\'ll be able to set the donation amounts. You even get to choose whether you want to create a whole page for your form, or embed it on a different page of your site.', 'give' ); ?></p>

					<p><?php _e( 'But all of these features begin simply by going to the menu and choosing "Add New Form."', 'give' ); ?></p>
				</div>

				<div class="content feature-section-item last-feature">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/admin/getting-started-add-new-form.png">
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item multi-level-gif">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/admin/getting-started-new-form-multi-level.gif">
				</div>

				<div class="content feature-section-item last-feature">
					<h3><?php _e( 'STEP 2: Choose Your Levels', 'give' ); ?></h3>

					<p><?php _e( 'Each Form can be set to receive either a pre-determined amount or have multiple suggested levels of giving. Choosing "Multi-level Donation" opens up the levels section where you can add as many levels as you like with your own custom names and amounts.', 'give' ); ?></p>
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item add-content">
					<h3><?php _e( 'STEP 3: Landing Page or Shortcode Mode?', 'give' ); ?></h3>

					<p><?php _e( 'Every form you create in Give can either become it\'s own stand-alone page, or it can be inserted into any other page or post throughout your site as a Shortcode.', 'give' ); ?></p>

					<p><?php _e( 'You can choose these different modes by going to the "Form Content" section. From there, you can choose to add content before or after the Donation form on a page, or if you choose "None" perhaps you want to instead use the shortcode. You can find the shortcode in the top right column directly under the Publish/Save button. This feature gives you the most amount of flexibility with controlling your content on your website all within the same page.', 'give' ); ?></p>
				</div>

				<div class="content feature-section-item last-feature">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/admin/getting-started-add-content.png">
				</div>

			</div>
			<!-- /.feature-section -->

			<div class="feature-section clearfix">

				<div class="content feature-section-item display-options">
					<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/admin/getting-started-display-options.png">
				</div>

				<div class="content feature-section-item last-feature">
					<h3><?php _e( 'STEP 4: Configure Your Display Options', 'give' ); ?></h3>

					<p><?php _e( 'Lastly, you can present the form in a lot of different ways. With the "Display Options" section you can configure how the credit card field appears, the submit button text, which Gateway you want to use, whether Guests (non-logged in users) can donate or not, and a log-in form.', 'give' ); ?></p>
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
			<h1 class="welcome-h1"><?php printf( __( 'Give %s - Credits', 'give' ), $display_version ); ?></h1>

			<?php give_social_media_elements() ?>

			<div class="about-text"><?php printf( __( 'Thanks to all those who have contributed code directly or indirectly. ', 'give' ), $display_version ); ?></div>

			<div class="about-text"><?php _e( 'Welcome to the getting started guide.' ); ?></div>

			<p class="newsletter-intro">Be sure to sign up for the Give newsletter below to stay informed of important updates and news.</p>

			<?php give_get_newsletter() ?>

			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Give is created by a dedicated team of developers. If you are interested in contributing please visit the <a href="https://github.com/WordImpress/give" target="_blank">GitHub Repo</a>.', 'give' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
	<?php
	}


	/**
	 * Parse the GIVE readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( GIVE_PLUGIN_DIR . 'readme.txt' ) ? GIVE_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changlog was found.', 'give' ) . '</p>';
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
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'give' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
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
		$contributors = get_transient( 'give_contributors' );

		if ( false !== $contributors ) {
			return $contributors;
		}

		$response = wp_remote_get( 'https://api.github.com/repos/WordImpress/Give/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) ) {
			return array();
		}

		set_transient( 'give_contributors', $contributors, 3600 );

		return $contributors;
	}

	/**
	 * Sends user to the Welcome page on first activation of GIVE as well as each
	 * time GIVE is upgraded to a new version
	 *
	 * @access public
	 * @since  1.0
	 * @global $give_options Array of all the GIVE Options
	 * @return void
	 */
	public function welcome() {
		global $give_options;


		// Bail if no activation redirect
		if ( ! get_transient( '_give_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_give_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		$upgrade = get_option( 'give_version_upgraded_from' );

		if ( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		} elseif( isset( $give_options['disable_welcome'] ) ) { // Welcome is disabled in settings

		} else { // Welcome is NOT disabled in settings
			wp_safe_redirect(admin_url('index.php?page=give-about'));
			exit;
		}
	}

}

new Give_Welcome();
