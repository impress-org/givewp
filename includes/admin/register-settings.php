<?php

/**
 *
 * Register Settings
 *
 * Include and setup custom metaboxes and fields.
 *
 * @package    Give
 * @subpackage Admin
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
class Give_Plugin_Settings {

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'give_settings';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );

		//Customize CMB2 URL
		add_filter( 'cmb2_meta_box_url', array( $this, 'give_update_cmb_meta_box_url' ) );

		//Custom CMB2 Settings Fields
		add_action( 'cmb2_render_give_title', 'give_title_callback', 10, 5 );
		add_action( 'cmb2_render_enabled_gateways', 'give_enabled_gateways_callback', 10, 5 );
		add_action( 'cmb2_render_default_gateway', 'give_default_gateway_callback', 10, 5 );
		add_action( 'cmb2_render_email_preview_buttons', 'give_email_preview_buttons_callback', 10, 5 );
		add_action( 'cmb2_render_system_info', 'give_system_info_callback', 10, 5 );
		add_action( 'cmb2_render_license_key', 'give_license_key_callback', 10, 5 );

	}

	/**
	 * Register our setting to WP
	 * @since  1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}


	/**
	 * Filter CMB2 URL
	 *
	 * @description: Required for CMB2 to properly load CSS/JS
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	public function give_update_cmb_meta_box_url( $url ) {
		//Path to Give's CMB
		return plugin_dir_url( __FILE__ ) . 'libraries/cmb2';
	}


	/**
	 * Retrieve settings tabs
	 *
	 * @since 1.0
	 * @return array $tabs
	 */
	public function give_get_settings_tabs() {

		$settings = $this->give_settings( null );

		$tabs             = array();
		$tabs['general']  = __( 'General', 'give' );
		$tabs['gateways'] = __( 'Payment Gateways', 'give' );
		$tabs['display']  = __( 'Display Options', 'give' );
		$tabs['emails']   = __( 'Emails', 'give' );

		if ( ! empty( $settings['addons']['fields'] ) ) {
			$tabs['addons'] = __( 'Add-ons', 'give' );
		}

		if ( ! empty( $settings['licenses']['fields'] ) ) {
			$tabs['licenses'] = __( 'Licenses', 'give' );
		}

		$tabs['system_info'] = __( 'System Info', 'give' );

		return apply_filters( 'give_settings_tabs', $tabs );
	}


	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  1.0
	 */
	public function admin_page_display() {

		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->give_get_settings_tabs() ) ? $_GET['tab'] : 'general';

		?>

		<div class="wrap give_settings_page cmb2_options_page <?php echo $this->key; ?>">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->give_get_settings_tabs() as $tab_id => $tab_name ) {

					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );

					echo '</a>';
				}
				?>
			</h2>

			<?php cmb2_metabox_form( $this->give_settings( $active_tab ), $this->key ); ?>

		</div><!-- .wrap -->

	<?php
	}

	/**
	 * Define General Settings Metabox and field configurations.
	 *
	 * Filters are provided for each settings section to allow add-ons and other plugins to add their own settings
	 *
	 * @param $active_tab active tab settings; null returns full array
	 *
	 * @return array
	 */
	public function give_settings( $active_tab ) {

		$give_settings = array(
			/**
			 * General Settings
			 */
			'general'     => array(
				'id'         => 'options_page',
				'give_title' => __( 'General Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_general', array(
						array(
							'name' => __( 'General Settings', 'give' ),
							'desc' => '<hr>',
							'type' => 'give_title',
							'id'   => 'give_title'
						),
						array(
							'name' => __( 'Test Mode', 'give' ),
							'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'give' ),
							'id'   => 'test_mode',
							'type' => 'checkbox'
						),
						array(
							'name'    => __( 'Success Page', 'give' ),
							'desc'    => __( 'This is the page donators are sent to after completing their donations. The <code>[give_receipt]</code> shortcode should be on this page.', 'give' ),
							'id'      => 'success_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => __( 'Failed Transaction Page', 'give' ),
							'desc'    => __( 'This is the page donators are sent to if their transaction is cancelled or fails.', 'give' ),
							'id'      => 'failure_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => __( 'Donation History Page', 'give' ),
							'desc'    => __( 'This page shows a complete donation history for the current user. The <code>[donation_history]</code> shortcode should be on this page.', 'give' ),
							'id'      => 'history_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => __( 'Base Country', 'give' ),
							'desc'    => __( 'Where does your site operate from?', 'give' ),
							'id'      => 'base_country',
							'type'    => 'select',
							'options' => give_get_country_list(),
						),
						array(
							'name' => __( 'Currency Settings', 'give' ),
							'desc' => '<hr>',
							'type' => 'give_title',
							'id'   => 'give_title'
						),
						array(
							'name'    => __( 'Currency', 'cmb' ),
							'desc'    => 'Choose your currency. Note that some payment gateways have currency restrictions.',
							'id'      => 'currency',
							'type'    => 'select',
							'options' => give_get_currencies(),
							'default' => 'USD',
						),
						array(
							'name'    => __( 'Currency Position', 'cmb' ),
							'desc'    => 'Choose the position of the currency sign.',
							'id'      => 'currency_position',
							'type'    => 'select',
							'options' => array(
								'before' => __( 'Before - $10', 'give' ),
								'after'  => __( 'After - 10$', 'give' )
							),
							'default' => 'before',
						),
						array(
							'name'    => __( 'Thousands Separator', 'give' ),
							'desc'    => __( 'The symbol (typically , or .) to separate thousands', 'give' ),
							'id'      => 'thousands_separator',
							'type'    => 'text_small',
							'default' => ',',
						),
						array(
							'name'    => __( 'Decimal Separator', 'give' ),
							'desc'    => __( 'The symbol (usually , or .) to separate decimal points', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text_small',
							'default' => '.',
						),
					)
				)
			),
			/**
			 * Payment Gateways
			 */
			'gateways'    => array(
				'id'         => 'options_page',
				'give_title' => __( 'Payment Gateways', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_gateways', array(
						array(
							'name' => __( 'Gateways Settings', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'name' => __( 'Enabled Gateways', 'give' ),
							'desc' => __( 'Choose the payment gateways you would like enabled.', 'give' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),
						array(
							'name' => __( 'Default Gateway', 'give' ),
							'desc' => __( 'This is the gateway that will be selected by default.', 'give' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway'
						),
						array(
							'name' => __( 'PayPal Standard', 'give' ),
							'desc' => '<hr>',
							'type' => 'give_title',
							'id'   => 'give_title',
						),
						array(
							'name' => __( 'PayPal Email', 'give' ),
							'desc' => __( 'Enter your PayPal account\'s email', 'give' ),
							'id'   => 'paypal_email',
							'type' => 'text_email',
						),
						array(
							'name' => __( 'PayPal Page Style', 'give' ),
							'desc' => __( 'Enter the name of the page style to use, or leave blank to use the default', 'give' ),
							'id'   => 'paypal_page_style',
							'type' => 'text',
						),
						array(
							'name' => __( 'Disable PayPal IPN Verification', 'give' ),
							'desc' => __( 'If donations are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying donations.', 'give' ),
							'id'   => 'disable_paypal_verification',
							'type' => 'checkbox'
						),
						array(
							'name' => __( 'Offline Donations', 'give' ),
							'desc' => '<hr>',
							'type' => 'give_title',
							'id'   => 'give_title',
						),
						array(
							'name'    => __( 'Global Offline Donation Text', 'give' ),
							'desc'    => __( 'The following content will appear for all forms when the user selects the offline donation payment option. Note: You may customize the content per form as needed.', 'give' ),
							'id'      => 'global_offline_donation_content',
							'default' => give_get_default_offline_donation_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
					)
				)
			),
			/** Display Settings */
			'display'     => array(
				'id'         => 'options_page',
				'give_title' => __( 'Display Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_display', array(
						array(
							'name' => __( 'Display Settings', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'name' => __( 'Disable CSS', 'give' ),
							'desc' => __( 'Enable this option if you would like to disable all of Give\'s included CSS stylesheets.', 'give' ),
							'id'   => 'disable_css',
							'type' => 'checkbox'
						),
						array(
							'name' => __( 'Post Types', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'name' => __( 'Disable Form\'s Single View', 'give' ),
							'desc' => __( 'By default, all forms have single views enabled which create a specific URL on your website for that form. This option disables the singular and archive views from being publicly viewable. Note: you will need to embed forms using a shortcode or widget if enabled.', 'give' ),
							'id'   => 'disable_forms_singular',
							'type' => 'checkbox'
						),
						array(
							'name' => __( 'Disable Form\'s Archives', 'give' ),
							'desc' => __( 'Archives pages list all the forms you have created. This option will disable only the form\'s archive page(s). The single form\'s view will remain in place. Note: you will need to refresh your permalinks after this option has been enabled.', 'give' ),
							'id'   => 'disable_forms_archives',
							'type' => 'checkbox'
						),
						array(
							'name' => __( 'Taxonomies', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'name' => __( 'Enable Form Categories', 'give' ),
							'desc' => __( 'Check this option if you would like to categorize your donation forms. This option enables the form\'s category taxonomy.', 'give' ),
							'id'   => 'enable_categories',
							'type' => 'checkbox'
						),
						array(
							'name' => __( 'Enable Form Tags', 'give' ),
							'desc' => __( 'Check this option if you would like to tag your donation forms. This option enables the form\'s tag taxonomy.', 'give' ),
							'id'   => 'enable_tags',
							'type' => 'checkbox'
						),
					)
				)

			),
			/**
			 * Emails Options
			 */
			'emails'      => array(
				'id'         => 'options_page',
				'give_title' => __( 'Give Email Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_emails', array(
						array(
							'name' => __( 'Email Settings', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'id'      => 'email_template',
							'name'    => __( 'Email Template', 'give' ),
							'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Donation Receipt" to see the new template.', 'give' ),
							'type'    => 'select',
							'options' => give_get_email_templates()
						),
						array(
							'id'   => 'email_logo',
							'name' => __( 'Logo', 'give' ),
							'desc' => __( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'give' ),
							'type' => 'file'
						),
						array(
							'id'      => 'from_name',
							'name'    => __( 'From Name', 'give' ),
							'desc'    => __( 'The name donation receipts are said to come from. This should probably be your site or shop name.', 'give' ),
							'default' => get_bloginfo( 'name' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'from_email',
							'name'    => __( 'From Email', 'give' ),
							'desc'    => __( 'Email to send donation receipts from. This will act as the "from" and "reply-to" address.', 'give' ),
							'default' => get_bloginfo( 'admin_email' ),
							'type'    => 'text'
						),
						array(
							'name' => __( 'Donation Receipt', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'id'      => 'donation_subject',
							'name'    => __( 'Donation Email Subject', 'give' ),
							'desc'    => __( 'Enter the subject line for the donation receipt email', 'give' ),
							'default' => __( 'Donation Receipt', 'give' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'donation_receipt',
							'name'    => __( 'Donation Receipt', 'give' ),
							'desc'    => __( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted. Available template tags:', 'give' ) . '<br/>' . give_get_emails_tags_list(),
							'type'    => 'wysiwyg',
							'default' => __( "Dear", "give" ) . " {name},\n\n" . __( "Thank you for your donation. Your generosity is appreciated! Please click on the link below to view your receipt.", "give" ) . "\n\n{receipt_link}\n\nSincerely,\n{sitename}"
						),
						array(
							'name' => __( 'New Donation Notification', 'give' ),
							'desc' => '<hr>',
							'id'   => 'give_title',
							'type' => 'give_title'
						),
						array(
							'id'      => 'donation_notification_subject',
							'name'    => __( 'Donation Notification Subject', 'give' ),
							'desc'    => __( 'Enter the subject line for the donation notification email', 'give' ),
							'type'    => 'text',
							'default' => __( 'New Donation - #{payment_id}', 'give' )
						),
						array(
							'id'      => 'donation_notification',
							'name'    => __( 'Donation Notification', 'give' ),
							'desc'    => __( 'Enter the email that is sent to donation notification emails after completion of a donation. HTML is accepted. Available template tags:', 'give' ) . '<br/>' . give_get_emails_tags_list(),
							'type'    => 'wysiwyg',
							'default' => give_get_default_donation_notification_email()
						),
						array(
							'id'      => 'admin_notice_emails',
							'name'    => __( 'Donation Notification Emails', 'give' ),
							'desc'    => __( 'Enter the email address(es) that should receive a notification anytime a donation is made, one per line', 'give' ),
							'type'    => 'textarea',
							'default' => get_bloginfo( 'admin_email' )
						),
						array(
							'id'   => 'disable_admin_notices',
							'name' => __( 'Disable Admin Notifications', 'give' ),
							'desc' => __( 'Check this box if you do not want to receive emails when new donations are made.', 'give' ),
							'type' => 'checkbox'
						)
					)
				)
			),
			/** Extension Settings */
			'addons'      => array(
				'id'         => 'options_page',
				'give_title' => __( 'Give Add-ons Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_addons', array()
				)
			),
			/** Licenses Settings */
			'licenses'    => array(
				'id'         => 'options_page',
				'give_title' => __( 'Give Licenses', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_licenses', array()
				)
			),
			/** Licenses Settings */
			'system_info' => array(
				'id'         => 'options_page',
				'give_title' => __( 'System Info', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_system', array(
						array(
							'id'   => 'system_info',
							'name' => __( 'System Info', 'give' ),
							'desc' => __( 'Please copy and paste this information in your ticket when contacting support.', 'give' ),
							'type' => 'system_info'
						)
					)
				)
			),
		);

		//Return all settings array if necessary
		if ( $active_tab === null ) {
			return apply_filters( 'give_registered_settings', $give_settings );
		}

		// Add other tabs and settings fields as needed
		return apply_filters( 'give_registered_settings', $give_settings[ $active_tab ] );

	}


	/**
	 * Public getter method for retrieving protected/private variables
	 *
	 * @since  1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'fields', 'give_title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		if ( 'option_metabox' === $field ) {
			return $this->option_metabox();
		}

		throw new Exception( 'Invalid property: ' . $field );
	}


}

// Get it started
$Give_Settings = new Give_Plugin_Settings();

/**
 * Wrapper function around cmb2_get_option
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
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 *
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_enabled_gateways_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$field_description = $field_type_object->field->args['desc'];
	$gateways          = give_get_payment_gateways();

	echo '<ul class="cmb2-checkbox-list cmb2-list">';

	foreach ( $gateways as $key => $option ) :

		if ( is_array( $escaped_value ) && array_key_exists( $key, $escaped_value ) ) {
			$enabled = '1';
		} else {
			$enabled = null;
		}

		echo '<li><input name="' . $id . '[' . $key . ']" id="' . $id . '[' . $key . ']" type="checkbox" value="1" ' . checked( '1', $enabled, false ) . '/>&nbsp;';
		echo '<label for="' . $id . '[' . $key . ']">' . $option['admin_label'] . '</label></li>';

	endforeach;

	if ( $field_description ) {
		echo '<p class="cmb2-metabox-description">' . $field_description . '</p>';
	}

	echo '</ul>';


}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.0
 *
 * @param $field_object , $escaped_value, $object_id, $object_type, $field_type_object Arguments passed by CMB2
 *
 * @return void
 */
function give_default_gateway_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$field_description = $field_type_object->field->args['desc'];
	$gateways          = give_get_payment_gateways();

	echo '<select class="cmb2_select" name="' . $id . '" id="' . $id . '">';

	//Add a field to the Give Form admin single post view of this field
	if ( $field_type_object->field->object_type === 'post' ) {
		echo '<option value="global">' . __( 'Global Default', 'give' ) . '</option>';
	}

	foreach ( $gateways as $key => $option ) :

		$selected = isset( $escaped_value ) ? selected( $key, $escaped_value, false ) : '';


		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';

	endforeach;

	echo '</select>';

	echo '<p class="cmb2-metabox-description">' . $field_description . '</p>';

}

/**
 * Give Title
 *
 * Renders custom section titles output; Really only an <hr> because CMB2's output is a bit funky
 *
 * @since 1.0
 *
 * @param       $field_object , $escaped_value, $object_id, $object_type, $field_type_object
 *
 * @return void
 */
function give_title_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$title             = $field_type_object->field->args['name'];
	$field_description = $field_type_object->field->args['desc'];

	?>
	<hr>
<?php
}

/**
 * Gets a number of posts and displays them as options
 *
 * @param  array $query_args Optional. Overrides defaults.
 * @param  bool  $force      Force the pages to be loaded even if not on settings
 *
 * @see: https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-field-types
 * @return array An array of options that matches the CMB2 options array
 */
function give_cmb2_get_post_options( $query_args, $force = false ) {

	$post_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'give-settings' != $_GET['page'] ) && ! $force ) {
		return $post_options;
	}

	$args = wp_parse_args( $query_args, array(
		'post_type'   => 'page',
		'numberposts' => 10,
	) );

	$posts = get_posts( $args );

	if ( $posts ) {
		foreach ( $posts as $post ) {

			$post_options[ $post->ID ] = $post->post_title;

		}
	}

	return $post_options;
}


/**
 * Modify CMB2 Default Form Output
 *
 * @param string @args
 *
 * @since 1.0
 */

add_filter( 'cmb2_get_metabox_form_format', 'give_modify_cmb2_form_output', 10, 3 );

function give_modify_cmb2_form_output( $form_format, $object_id, $cmb ) {

	//only modify the give settings form
	if ( 'give_settings' == $object_id && 'options_page' == $cmb->cmb_id ) {

		return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="give-submit-wrap"><input type="submit" name="submit-cmb" value="' . __( 'Save Settings', 'give' ) . '" class="button-primary"></div></form>';
	}

	return $form_format;

}


/**
 * Give License Key Callback
 *
 * @description Registers the license field callback for EDD's Software Licensing
 * @since       1.0
 *
 * @param array $field_object , $escaped_value, $object_id, $object_type, $field_type_object Arguments passed by CMB2
 *
 * @return void
 */
if ( ! function_exists( 'give_license_key_callback' ) ) {
	function give_license_key_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

		$id                = $field_type_object->field->args['id'];
		$field_description = $field_type_object->field->args['desc'];
		$license_status    = get_option( $field_type_object->field->args['options']['is_valid_license_option'] );
		$field_classes     = 'regular-text give-license-field';

		if ( $license_status === 'valid' ) {
			$field_classes .= ' give-license-active';
		}

		$html = $field_type_object->input(
			array(
				'class' => $field_classes,
				'type'  => 'text'
			) );

		if ( $license_status === 'valid' ) {
			$html .= '<input type="submit" class="button-secondary give-license-deactivate" name="' . $id . '_deactivate" value="' . __( 'Deactivate License', 'give' ) . '"/>';
		}

		$html .= '<label for="give_settings[' . $id . ']"> ' . $field_description . '</label>';

		wp_nonce_field( $id . '-nonce', $id . '-nonce' );

		echo $html;
	}
}


/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function give_hook_callback( $args ) {
	do_action( 'give_' . $args['id'] );
}

/**
 * Get the CMB2 bootstrap!
 *
 * Super important!
 */
require_once GIVE_PLUGIN_DIR . '/includes/admin/libraries/cmb2/init.php';