<?php
/**
 * Weclome Page Class
 *
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
				border-bottom: 1px solid #CCC;
				padding: 0 0 20px;
				margin: 0 0 20px;
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
			})( window.jQuery || window.Zepto )
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
	 * @since  1.9
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'give-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'give-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'give' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'give-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'give-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'give' ); ?>
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
			<h1><?php printf( __( 'Welcome to Give %s', 'give' ), $display_version ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Give allows you to quickly and easily accept donations with little barrier to entry.', 'give' ), $display_version ); ?></div>
			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="feature-section col two-col clearfix introduction">

				<div class="video">
					<img src="http://placehold.it/500x280">
<!--					<iframe width="560" height="315" src="//www.youtube.com/embed/za43poLirX4" frameborder="0" allowfullscreen></iframe>-->
				</div>

				<div class="content last-feature">

					<h3><?php _e( 'Accept Donations Quickly and Easily', 'give' ); ?></h3>

					<p><?php _e( 'Getting started with Give is a breeze. How you use the plugin depends on the type of donations you wish to accept donations on your WordPress website. Give is powered by "Forms" which you can display across your website.', 'give' ); ?></p>

					<p><?php _e( 'Forms are completely customizable and can be placed on any WordPress page or post.', 'give' ); ?></p>

				</div>

			</div>
			<!-- /.intro-section -->

			<div class="feature-section col two-col clearfix">

				<div class="content">

					<h3><?php _e( 'Setting Up Your First Form', 'give' ); ?></h3>

					<p><?php _e( 'Give forms are the backbo.', 'give' ); ?></p>

					<p><?php _e( '.', 'give' ); ?></p>


				</div>

				<div class="content last-feature">

					<img src="http://placehold.it/500x280">

				</div>

			</div>
			<!-- /.feature-section -->
			<div class="feature-section col two-col clearfix">

				<div class="content">
					<img src="http://placehold.it/500x280">
				</div>

				<div class="content last-feature">

					<h3><?php _e( 'Managing a Campaign', 'give' ); ?></h3>

					<p><?php _e( 'Illas vis. Animal nunc secuit. Mortales sublime galeae! Surgere habitabilis inmensa! Caeli mentes zonae hunc reparabat. Regio quisque. Modo spisso. Haec pondere. Mundi caeca campoque adhuc onerosior. Omni caelumque dicere quicquam volucres. Onerosior coeptis igni tepescunt addidit. Est unus septemque astra locis summaque.', 'give' ); ?></p>

					<p><?php _e( 'Magni nullo frigida mundo satus iussit vix? Parte mare dissociata. Ignea finxit lacusque regio fecit fratrum umor habentem duris. Adsiduis fratrum mixtam glomeravit longo.', 'give' ); ?></p>

				</div>

			</div>
			<!-- /.feature-section -->
			<div class="feature-section col two-col clearfix">

				<div class="content">

					<h3><?php _e( 'Start Accepting Donations', 'give' ); ?></h3>

					<p><?php _e( 'Illas vis. Animal nunc secuit. Mortales sublime galeae! Surgere habitabilis inmensa! Caeli mentes zonae hunc reparabat. Regio quisque. Modo spisso. Haec pondere. Mundi caeca campoque adhuc onerosior. Omni caelumque dicere quicquam volucres. Onerosior coeptis igni tepescunt addidit. Est unus septemque astra locis summaque.', 'give' ); ?></p>

					<p><?php _e( 'Magni nullo frigida mundo satus iussit vix? Parte mare dissociata. Ignea finxit lacusque regio fecit fratrum umor habentem duris. Adsiduis fratrum mixtam glomeravit longo.', 'give' ); ?></p>


				</div>

				<div class="content last-feature">

					<img src="http://placehold.it/500x280">

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
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Give %s', 'give' ), $display_version ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version of Give!', 'give' ), $display_version ); ?></div>
			<div class="give-badge"><?php printf( __( 'Version %s', 'give' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Use the tips below to get started using Give. You will be up and running in no time!', 'give' ); ?></p>


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
			<h1><?php printf( __( 'Welcome to Give %s', 'give' ), $display_version ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Give %s is ready to make your online store faster, safer and better!', 'give' ), $display_version ); ?></div>
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

		$response = wp_remote_get( 'https://api.github.com/repos/WordImpress/give/contributors', array( 'sslverify' => false ) );

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
			wp_safe_redirect( admin_url( 'index.php?page=give-getting-started' ) );
			exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		}
	}

}

new Give_Welcome();
