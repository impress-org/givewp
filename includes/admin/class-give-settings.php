<?php

/**
 * Class Give_Settings
 */
class Give_CMB2_Settings_Tabs {

	/**
	 * Whether settings notices have already been set
	 *
	 * @var bool
	 *
	 * @since  1.0.0
	 */
	protected static $once = false;

	/**
	 * Options page hook, equivalent to get_current_screen()['id']
	 *
	 * @var string
	 *
	 * @since  1.0.0
	 */
	protected static $options_page = '';

	/**
	 * $props: Properties which can be injected via constructor
	 * Note that
	 *
	 * @var array
	 *
	 * @since  1.0.0
	 */
	private static $props = array(
		'key'      => 'my_options',              // WP options slug
		'title'    => 'My Options',              // Page title
		'topmenu'  => '',                        // See 'parent_slug' in menuargs
		'postslug' => '',                        // Slug of a custom post type
		'menuargs' => array(
			'parent_slug' => '',                   // Existing WP menu, blank for new top-level menu
			'page_title'  => '',                   // Will be set from "title" if blank
			'menu_title'  => '',                   // WP menu title
			'capability'  => 'manage_options',     // Admin capability needed to access options page
			'menu_slug'   => '',                   // Menu slug
			'icon_url'    => '',                   // URL of WP admin icon
			'position'    => null,                 // Integer required, null places at bottom of admin menu
		),
		'jsuri'    => '',                        // Location of JS if not in same directory as this class
		'boxes'    => array(),                   // Array of CMB2 metaboxes
		'tabs'     => array(),                   // Array of tab config arrays
		'cols'     => 1,                         // Allows use of sidebar
		'savetxt'  => 'Save',                    // Text on the save button, blank removes button
	);

	/**
	 * CONSTRUCT
	 * Inject anything within the self::$props array by matching the argument keys.
	 *
	 * @param array $args Array of arguments
	 *
	 * @throws \Exception
	 *
	 * @since  1.0.2 recurse the menuargs array with internal function instead of wp_parse_args()
	 * @since  1.0.0
	 */
	public function __construct( $args ) {

		// require CMB2
		if ( ! class_exists( 'CMB2' ) ) {
			throw new Exception( 'CMB2_Metatabs_Options: CMB2 is required to use this class.' );
		}

		// parse any injected arguments and add to self::$props
		self::$props = self::parse_args_r( $args, self::$props );

		// validate the properties we were sent
		$this->validate_props();

		// add tabs: several actions depend on knowing if tabs are present
		self::$props['tabs'] = $this->add_tabs();

		// Add actions
		$this->add_wp_actions();
	}

	/**
	 * PARSE ARGUMENTS RECURSIVELY
	 * Allows us to merge multidimensional properties
	 *
	 * Thanks: https://gist.github.com/boonebgorges/5510970
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return array
	 *
	 * @since 1.0.2
	 */
	public static function parse_args_r( &$a, $b ) {
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}

	/**
	 * VALIDATE PROPS
	 * Checks the values of critical passed properties
	 *
	 * @throws \Exception
	 *
	 * @since 1.0.2 Removed validation of menu args. Sending a plain array will no longer work!
	 * @since 1.0.1 Moved menuargs validation to within this method
	 * @since 1.0.0
	 */
	private function validate_props() {

		// set JS url
		if ( ! self::$props['jsuri'] ) {
			self::$props['jsuri'] = plugin_dir_url( __FILE__ ) . 'cmb2multiopts.js';
		}

		// set columns to 1 if illegal value sent
		self::$props['cols'] = intval( self::$props['cols'] );
		if ( self::$props['cols'] > 2 || self::$props['cols'] < 1 ) {
			self::$props['cols'] = 1;
		}
	}

	/**
	 * ADD WP ACTIONS
	 * Note, some additional actions are added elsewhere as they cannot be added this early.
	 *
	 * @since  1.0.0
	 */
	private function add_wp_actions() {
		// Register setting
		add_action( 'admin_init', array( $this, 'register_setting' ) );

		// Adds page to admin with menu entry
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 12 );

		// Include CSS for this options page as style tag in head if tabs are configured
		add_action( 'admin_head', array( $this, 'add_css' ) );

		// Adds JS to foot
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );

		// Adds custom save button field, allowing save button to be added to metaboxes
		add_action( 'cmb2_render_options_save_button', array( $this, 'render_save_button' ), 10, 1 );
	}

	/**
	 * REGISTER SETTING
	 *
	 * @since  1.0.0
	 */
	public function register_setting() {
		register_setting( self::$props['key'], self::$props['key'] );
	}

	/**
	 * ADD OPTIONS PAGE
	 *
	 * @since 1.0.2 Moved the callback determination to build_menu_args()
	 * @since 1.0.0
	 */
	public function add_options_page() {

		// build arguments
		$args = $this->build_menu_args();

		// this is kind of ugly, but so is the WP function!
		self::$options_page = $args['cb']( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6] );

		// Include CMB CSS in the head to avoid FOUC, called here as we need the screen ID
		add_action( 'admin_print_styles-' . self::$options_page, array( 'CMB2_hookup', 'enqueue_cmb_css' ) );

		// Adds existing metaboxes, see note in function, called here as we need the screen ID
		add_action( 'add_meta_boxes_' . self::$options_page, array( $this, 'add_metaboxes' ) );

		// On page load, do "metaboxes" actions, called here as we need the screen ID
		add_action( 'load-' . self::$options_page, array( $this, 'do_metaboxes' ) );
	}

	/**
	 * BUILD MENU ARGS
	 * Builds the arguments needed to add options page to admin menu if they are not injected.
	 *
	 * Including either self::$props['topmenu'] or self::$props['menuargs']['parent_slug'] will trigger the creation
	 * of a submenu, otherwise a new top menu will be added.
	 *
	 * @return array
	 *
	 * @since 1.0.2 Removed counting of menu arguments, function now uses menuargs keys
	 * @since 1.0.1 Removed menuargs validation to validate_props() method
	 * @since 1.0.0
	 */
	private function build_menu_args() {

		$args = array();

		// set the top menu if either topmenu or the menuargs parent_slug is set
		$parent = self::$props['topmenu'] ? self::$props['topmenu'] : '';
		$parent = self::$props['menuargs']['parent_slug'] ?
			self::$props['menuargs']['parent_slug'] : $parent;

		// set the page title; overrides 'title' with menuargs 'page_title' if set
		$pagetitle = self::$props['menuargs']['page_title'] ?
			self::$props['menuargs']['page_title'] : self::$props['title'];

		// sub[0] : parent slug
		if ( $parent ) {
			// add a post_type get variable, to allow post options pages, if set
			$add    = self::$props['postslug'] ? '?post_type=' . self::$props['postslug'] : '';
			$args[] = $parent . $add;
		}

		// top[0], sub[1] : page title
		$args[] = $pagetitle;

		// top[1], sub[2] : menu title, defaults to page title if not set
		$args[] = self::$props['menuargs']['menu_title'] ?
			self::$props['menuargs']['menu_title'] : $pagetitle;

		// top[2], sub[3] : capability
		$args[] = self::$props['menuargs']['capability'];

		// top[3], sub[4] : menu_slug, defaults to options slug if not set
		$args[] = self::$props['menuargs']['menu_slug'] ?
			self::$props['menuargs']['menu_slug'] : self::$props['key'];

		// top[4], sub[5] : callable function
		$args[] = array( $this, 'admin_page_display' );

		// top menu icon and menu position
		if ( ! $parent ) {

			// top[5] icon url
			$args[] = self::$props['menuargs']['icon_url'] ?
				self::$props['menuargs']['icon_url'] : '';

			// top[6] menu position
			$args[] = intval( self::$props['menuargs']['position'] );
		} // sub[6] : unused, but returns consistent array
		else {
			$args[] = null;
		}

		// set which WP function will be called based on $parent
		$args['cb'] = $parent ? 'add_submenu_page' : 'add_menu_page';

		return $args;
	}

	/**
	 * ADD SCRIPTS
	 * Add WP's metabox script, either by itself or as dependency of the tabs script. Added only to this options page.
	 * If you roll your own script, note the localized values being passed here.
	 *
	 * @param string $hook_suffix
	 *
	 * @throws \Exception
	 *
	 * @since 1.0.1 Always add postbox toggle, removed toggle from tab handler JS
	 * @since 1.0.0
	 */
	public function add_scripts( $hook_suffix ) {

		// 'postboxes' needed for metaboxes to work properly
		wp_enqueue_script( 'postbox' );

		// toggle the postboxes
		add_action( 'admin_print_footer_scripts', array( $this, 'toggle_postboxes' ) );

		// only add the main script to the options page if there are tabs present
		if ( $hook_suffix !== self::$options_page || empty( self::$props['tabs'] ) ) {
			return;
		}

		// enqueue the script
		wp_enqueue_script( self::$props['key'] . '-admin', GIVE_PLUGIN_URL . 'assets/js/admin/cmb2multiopts.js', array( 'postbox' ), false, true );

		// localize script to give access to this page's slug
		wp_localize_script( self::$props['key'] . '-admin', 'cmb2OptTabs', array(
			'key'        => self::$props['key'],
			'posttype'   => self::$props['postslug'],
			'defaulttab' => self::$props['tabs'][0]['id'],
		) );
	}

	/**
	 * TOGGLE POSTBOXES
	 * Ensures boxes are toggleable on non tabs pages
	 *
	 * @since 1.0.0
	 */
	public function toggle_postboxes() {
		echo '<script>jQuery(document).ready(function(){postboxes.add_postbox_toggles("postbox-container");});</script>';
	}

	/**
	 * ADD CSS
	 * Adds a couple of rules to clean up WP styles if tabs are included
	 *
	 * @since 1.0.0
	 */
	public function add_css() {

		// if tabs are not being used, return
		if ( empty( self::$props['tabs'] ) ) {
			return;
		}

		// add css to clean up tab styles in admin when used in a postbox
		$css = '<style type="text/css">';
		$css .= '#poststuff h2.nav-tab-wrapper{padding-bottom:0;margin-bottom: 20px;}';
		$css .= '.opt-hidden{display:none;}';
		$css .= '#side-sortables{padding-top:22px;}';
		$css .= '</style>';

		echo $css;
	}

	/**
	 * ADD METABOXES
	 * Adds CMB2 metaboxes.
	 *
	 * @since  1.0.0
	 */
	public function add_metaboxes() {

		// get the metaboxes
		self::$props['boxes'] = $this->cmb2_metaboxes();

		foreach ( self::$props['boxes'] as $box ) {

			$id = $box->meta_box['id'];

			// add notice if settings are saved
			add_action( 'cmb2_save_options-page_fields_' . $id, array( $this, 'settings_notices' ), 10, 2 );

			// add callback if tabs are configured which hides metaboxes until moved into proper tabs if not in sidebar
			if ( ! empty( self::$props['tabs'] ) && $box->meta_box['context'] !== 'side' ) {
				add_filter( 'postbox_classes_' . self::$options_page . '_' . $id, array(
					$this,
					'hide_metabox_class'
				) );
			}

			// if boxes are closed by default...
			if ( $box->meta_box['closed'] ) {
				add_filter( 'postbox_classes_' . self::$options_page . '_' . $id, array(
					$this,
					'close_metabox_class'
				) );
			}

			// add meta box
			add_meta_box(
				$box->meta_box['id'],
				$box->meta_box['title'],
				array( $this, 'metabox_callback' ),
				self::$options_page,
				$box->meta_box['context'],
				$box->meta_box['priority']
			);
		}
	}

	/**
	 *
	 * HIDE METABOX CLASS
	 *
	 * The "hidden" class hides metaboxes until they have been moved to appropriate tab, if tabs are used.
	 *
	 * @param array $classes
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function hide_metabox_class( $classes ) {
		$classes[] = 'opt-hidden';

		return $classes;
	}

	/**
	 * CLOSE METABOX CLASS
	 * Adds class to closed-by-default metaboxes
	 *
	 * @param array $classes
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function close_metabox_class( $classes ) {
		$classes[] = 'closed';

		return $classes;
	}

	/**
	 * DO METABOXES
	 * Triggers the loading of our metaboxes on this screen.
	 *
	 * @since 1.0.0
	 */
	public function do_metaboxes() {
		do_action( 'add_meta_boxes_' . self::$options_page, null );
		do_action( 'add_meta_boxes', self::$options_page, null );
	}

	/**
	 * METABOX CALLBACK
	 * Builds the fields and saves them.
	 *
	 * @since 1.0.1 Refactored the save tests to method should_save()
	 * @since 1.0.0
	 */
	public static function metabox_callback() {

		// get the metabox, fishing the ID out of the arguments array
		$args = func_get_args();
		$cmb  = cmb2_get_metabox( $args[1]['id'], self::$props['key'] );

		// save fields
		if ( self::should_save( $cmb ) ) {
			$cmb->save_fields( self::$props['key'], $cmb->mb_object_type(), $_POST );
		}

		// show the fields
		$cmb->show_form();
	}

	/**
	 * SHOULD SAVE
	 * Determine whether the CMB2 object should be saved. All tests must be true, hence return false for
	 * any failure.
	 *
	 * @param \CMB2 $cmb
	 *
	 * @return bool
	 *
	 * @since 1.0.1
	 */
	private static function should_save( $cmb ) {
		// was this flagged to save fields?
		if ( ! $cmb->prop( 'save_fields' ) ) {
			return false;
		}
		// are these values set?
		if ( ! isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST[ $cmb->nonce() ] ) ) {
			return false;
		}
		// does the nonce match?
		if ( ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return false;
		}
		// does the object_id equal the settings key?
		if ( ! $_POST['object_id'] == self::$props['key'] ) {
			return false;
		}

		return true;
	}

	/**
	 * ADMIN PAGE DISPLAY
	 * Admin page markup.
	 *
	 * @since  1.0.0
	 */
	public function admin_page_display() {

		// Page wrapper
		echo '<div class="wrap cmb2-options-page ' . self::$props['key'] . '">';

		// form wraps all tabs
		echo '<form class="cmb-form" method="post" id="cmo-options-form" '
		     . 'enctype="multipart/form-data" encoding="multipart/form-data">';

		// hidden object_id field
		echo '<input type="hidden" name="object_id" value="' . self::$props['key'] . '">';

		// add postbox, which allows use of metaboxes
		echo '<div id="poststuff">';

		// main column
		echo '<div id="post-body" class="metabox-holder columns-' . self::$props['cols'] . '">';

		// if two columns are called for
		if ( self::$props['cols'] == 2 ) {

			// add markup for sidebar
			echo '<div id="postbox-container-1" class="postbox-container">';
			echo '<div id="side-sortables" class="meta-box-sortables ui-sortable">';

			// add sidebar metaboxes
			do_meta_boxes( self::$options_page, 'side', null );

			echo '</div></div>';  // close sidebar
		}

		// open postbox container
		echo '<div id="postbox-container-';
		echo self::$props['cols'] == 2 ? '2' : '1';
		echo '" class="postbox-container">';

		// add tabs; the sortables container is within each tab
		echo $this->render_tabs();

		// place normal boxes, note that 'normal' and 'advanced' are rendered together when using tabs
		do_meta_boxes( self::$options_page, 'normal', null );

		// place advanced boxes
		do_meta_boxes( self::$options_page, 'advanced', null );

		echo '</div>';  // close postbox container
		echo '</div>';  // close post-body
		echo '</div>';    // close postbox

		// add submit button if savetxt was included
		if ( self::$props['savetxt'] ) {
			echo '<div style="clear:both;">';
			self::render_save_button( self::$props['savetxt'] );
			echo '</div>';
		}

		echo '</form>';  // close form

		echo '</div>';  // close wrapper

		// reset the notices flag
		self::$once = false;
		
	}

	/**
	 * RENDER SAVE BUTTON
	 * If this was called in the context of a CMB2 field, use the "desc" for the save text.
	 *
	 * @param string|\CMB2_Field $field
	 *
	 * @since 1.0.0
	 */
	public static function render_save_button( $field = '' ) {
		$text = is_string( $field ) ? $field : $field->args['desc'];
		if ( $text ) {
			echo '<input type="submit" name="submit-cmb" value="' . $text . '" class="button-primary">';
		}
	}

	/**
	 * SETTINGS NOTICES
	 * Added a check to make sure its only called once for the page...
	 *
	 * @param string $object_id
	 * @param array $updated
	 *
	 * @since 1.0.1 updated text domain
	 * @since 1.0.0
	 */
	public function settings_notices( $object_id, $updated ) {

		// bail if this isn't a notice for this page or we've already added a notice
		if ( $object_id !== self::$props['key'] || empty( $updated ) || self::$once ) {
			return;
		}

		// add notifications
		add_settings_error( self::$props['key'] . '-notices', '', __( 'Settings updated.', 'cmb2' ), 'updated' );
		settings_errors( self::$props['key'] . '-notices' );

		// set the flag so we don't pile up notices
		self::$once = true;
	}

	/**
	 * RENDER TABS
	 * Echoes tabs if they've been configured. The containers will have their metaboxes moved into them by javascript.
	 *
	 * @since 1.0.0
	 */
	private function render_tabs() {

		if ( empty( self::$props['tabs'] ) ) {
			return '';
		}

		$containers = '';
		$tabs       = '';

		foreach ( self::$props['tabs'] as $tab ) {

			// add tabs navigation
			$tabs .= '<a href="#" id="opt-tab-' . $tab['id'] . '" class="nav-tab opt-tab" ';
			$tabs .= 'data-optcontent="#opt-content-' . $tab['id'] . '">';
			$tabs .= $tab['title'];
			$tabs .= '</a>';

			// add tabs containers, javascript will use the data attribute to move metaboxes to within proper tab
			$contents = implode( ',', $tab['boxes'] );

			// tab container markup
			$containers .= '<div class="opt-content" id="opt-content-' . $tab['id'] . '" ';
			$containers .= ' data-boxes="' . $contents . '">';
			$containers .= $tab['desc'];
			$containers .= '<div class="meta-box-sortables ui-sortable">';
			$containers .= '</div>';
			$containers .= '</div>';
		}

		// add the tab structure to the page
		$return = '<h2 class="nav-tab-wrapper">';
		$return .= $tabs;
		$return .= '</h2>';
		$return .= $containers;

		return $return;
	}

	/**
	 * CMB2 METABOXES
	 * Allows three methods of adding metaboxes:
	 *
	 * 1) Injected boxes are added to the boxes array
	 * 2) Add additional boxes (or boxes if none were injected) the usual way within this function
	 * 3) If array is still empty, call CMB2_Boxes::get_all();
	 *
	 * @return array|\CMB2[]
	 *
	 * @since 1.0.0
	 */
	private function cmb2_metaboxes() {
		// add any injected metaboxes
		$boxes = self::$props['boxes'];

		// if $boxes is still empty, see if they've been configured elsewhere in the program
		return empty( $boxes ) ? CMB2_Boxes::get_all() : $boxes;
	}

	/**
	 * ADD TABS
	 * Add tabs to your options page. The array is empty by default. You can inject them into the constructor,
	 * or add them here, or leave empty for no tabs.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function add_tabs() {
		// add any injected tabs
		$tabs = self::$props['tabs'];

		return $tabs;
	}
}

/**
 * Callback for 'cmb2_admin_init'.
 *
 * In this example, 'boxes' and 'tabs' call functions simply to separate "normal" CMB2 configuration
 * from unique CMO configuration.
 */
function give_cmb2_metatabs_options_go() {

	$options_key = 'give-settings';

	// configuration array
	$args = array(
		'key'      => $options_key, // WP options slug
		'title'    => '', // Page title
		'topmenu'  => 'edit.php', // See 'parent_slug' in menuargs
		'postslug' => 'give_forms', // Slug of a custom post type
		'jsuri'    => '', // Location of JS if not in same directory as this class
		'boxes'    => give_cmb2_metatabs_options_add_boxes( $options_key ),
		'tabs'     => give_cmb2_metatabs_options_add_tabs(),
		'cols'     => 1,  // Allows use of sidebar
		'savetxt'  => __( 'Update Settings', 'give' ), // Text on the save button, blank removes button
	);

	// create the options page
	new Give_CMB2_Settings_Tabs( $args );
}

// add action to hook option page creation to
add_action( 'cmb2_admin_init', 'give_cmb2_metatabs_options_go' );

/**
 * Add some boxes the normal CMB2 way. (Five boxes and their fields, in this example.)
 *
 * This is typical CMB2, but note two crucial extra items:
 *
 * - the ['show_on'] property is configured
 * - a call to object_type method
 *
 * See the wiki for more detail on why these are important and what their values are.
 *
 * @param string $options_key
 *
 * @return array
 */
function give_cmb2_metatabs_options_add_boxes( $options_key ) {

	// holds all CMB2 box objects
	$boxes = array();

	// we will be adding this to all boxes
	$show_on = array(
		'key'   => 'options-page',
		'value' => array( $options_key ),
	);

	// first box
	$cmb = new_cmb2_box( array(
		'id'      => 'ex_dogs',
		'title'   => __( 'Internet Doggies', 'cmb2' ),
		'show_on' => $show_on, // critical, see wiki for why
	) );
	$cmb->add_field( array(
		'name' => __( 'That\'s a Good Dog!', 'cmb2' ),
		'desc' => __( 'What do you say when you see a dog on the internet?', 'cmb2' ),
		'id'   => 'ex_dogs_say',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Repeated How Many Times?', 'cmb2' ),
		'desc' => __( 'To the nearest multiple 3, how many times do you say it?', 'cmb2' ),
		'id'   => 'ex_dogs_repeat',
		'type' => 'text_small',
	) );
	$cmb->object_type( 'options-page' );  // critical, see wiki for why
	$boxes[] = $cmb;

	// second box
	$cmb = new_cmb2_box( array(
		'id'      => 'ex_cats',
		'title'   => __( 'Internet Kitties', 'cmb2' ),
		'show_on' => $show_on,
	) );
	$cmb->add_field( array(
		'name' => __( 'Nice kitty!', 'cmb2' ),
		'desc' => __( 'What do you say when you see a cat on the internet?', 'cmb2' ),
		'id'   => 'ex_cats_say',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Repeated How Many Times?', 'cmb2' ),
		'desc' => __( 'To the nearest multiple 3, how many times do you say it?', 'cmb2' ),
		'id'   => 'ex_cats_repeat',
		'type' => 'text_small',
	) );
	$cmb->object_type( 'options-page' );
	$boxes[] = $cmb;

	// third box
	$cmb = new_cmb2_box( array(
		'id'      => 'ex_healthy',
		'title'   => __( 'Eating for Good Health', 'cmb2' ),
		'show_on' => $show_on,
	) );
	$cmb->add_field( array(
		'name' => __( 'What is a healthy food?', 'cmb2' ),
		'desc' => __( 'Examples: Apple, Ding Dong', 'cmb2' ),
		'id'   => 'ex_healthy_food',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'How Many Servings?', 'cmb2' ),
		'desc' => __( 'How many times per day should you eat this?', 'cmb2' ),
		'id'   => 'ex_healthy_servings',
		'type' => 'text_small',
	) );
	$cmb->object_type( 'options-page' );
	$boxes[] = $cmb;

	// fourth box
	$cmb = new_cmb2_box( array(
		'id'      => 'ex_bad',
		'title'   => __( 'Foods to Avoid', 'cmb2' ),
		'show_on' => $show_on,
	) );
	$cmb->add_field( array(
		'name' => __( 'What is an unhealthy food?', 'cmb2' ),
		'desc' => __( 'Examples: Apple, not Ding Dong', 'cmb2' ),
		'id'   => 'ex_bad_food',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'How Many Pushups?', 'cmb2' ),
		'desc' => __( 'To the nearest 1, how many pushups do you need to counter your bad decision?', 'cmb2' ),
		'id'   => 'ex_bad_servings',
		'type' => 'text_small',
	) );
	$cmb->object_type( 'options-page' );
	$boxes[] = $cmb;

	// fifth box
	$cmb = new_cmb2_box( array(
		'id'      => 'ex_side',
		'title'   => __( 'Judging', 'cmb2' ),
		'show_on' => $show_on,
		'context' => 'side'
	) );
	$cmb->add_field( array(
		'name' => '',
		'desc' => __( 'This example page offers no judgment on your choices.', 'cmb2' ),
		'id'   => 'ex_judge',
		'type' => 'title',
	) );
	$cmb->object_type( 'options-page' );
	$boxes[] = $cmb;

	return $boxes;
}


/**
 * Add some tabs (in this case, two).
 *
 * Tabs are completely optional and removing them would result in the option metaboxes displaying sequentially.
 *
 * If you do configure tabs, all boxes whose context is "normal" or "advanced" must be in a tab to display.
 *
 * @return array
 */
function give_cmb2_metatabs_options_add_tabs() {

	$tabs = array();

	$tabs[] = array(
		'id'    => 'ex_tab1',
		'title' => 'Critters',
		'desc'  => '<p>Everyone likes dogs and/or cats, right?</p>',
		'boxes' => array(
			'ex_cats',
			'ex_dogs',
		),
	);
	$tabs[] = array(
		'id'    => 'ex_tab2',
		'title' => 'Eats',
		'desc'  => '',
		'boxes' => array(
			'ex_healthy',
			'ex_bad',
		),
	);

	return $tabs;
}

/**
 * Get Settings
 *
 * Retrieves all Give plugin settings
 *
 * @since 1.0
 * @return array Give settings
 */
function give_get_settings() {

	$settings = get_option( 'give_settings' );

	return (array) apply_filters( 'give_get_settings', $settings );

}

/**
 * Wrapper function around cmb2_get_option
 *
 * @since  0.1.0
 *
 * @param  string $key Options array key
 *
 * @return mixed        Option value
 */
function give_get_option( $key = '', $default = false ) {
	global $give_options;
	$value = ! empty( $give_options[ $key ] ) ? $give_options[ $key ] : $default;
	$value = apply_filters( 'give_get_option', $value, $key, $default );

	return apply_filters( 'give_get_option_' . $key, $value, $key, $default );
}

/**
 * Get the CMB2 bootstrap!
 *
 * @description: Checks to see if CMB2 plugin is installed first the uses included CMB2; we can still use it even it it's not active. This prevents fatal error conflicts with other themes and users of the CMB2 WP.org plugin
 *
 */

if ( file_exists( WP_PLUGIN_DIR . '/cmb2/init.php' ) && ! defined( 'CMB2_LOADED' ) ) {
	require_once WP_PLUGIN_DIR . '/cmb2/init.php';
} elseif ( file_exists( GIVE_PLUGIN_DIR . '/includes/libraries/cmb2/init.php' ) && ! defined( 'CMB2_LOADED' ) ) {
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/cmb2/init.php';
} elseif ( file_exists( GIVE_PLUGIN_DIR . '/includes/libraries/CMB2/init.php' ) && ! defined( 'CMB2_LOADED' ) ) {
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/CMB2/init.php';
}