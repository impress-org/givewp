<?php

/**
 * Class Give_Plugin_Settings
 *
 * Register settings Include and setup custom metaboxes and fields.
 *
 * @package    Give
 * @subpackage Admin
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @link       https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
class Give_Plugin_Settings {

	/**
	 * Option key, and option page slug.
	 *
	 * @var string
	 */
	private $key = 'give_settings';

	/**
	 * Array of metaboxes/fields.
	 *
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Give_Plugin_Settings constructor.
	 */
	public function __construct() {

		//Custom CMB2 Settings Fields
		add_action( 'cmb2_render_give_title', 'give_title_callback', 10, 5 );
		add_action( 'cmb2_render_give_description', 'give_description_callback', 10, 5 );
		add_action( 'cmb2_render_enabled_gateways', 'give_enabled_gateways_callback', 10, 5 );
		add_action( 'cmb2_render_default_gateway', 'give_default_gateway_callback', 10, 5 );
		add_action( 'cmb2_render_email_preview_buttons', 'give_email_preview_buttons_callback', 10, 5 );
		add_action( 'cmb2_render_system_info', 'give_system_info_callback', 10, 5 );
		add_action( 'cmb2_render_api', 'give_api_callback', 10, 5 );
		add_action( 'cmb2_render_license_key', 'give_license_key_callback', 10, 5 );
	}


	/**
	 * Register our setting to WP
	 *
	 * @since  1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );

	}


	/**
	 * Filter CMB2 URL
	 *
	 * Required for CMB2 to properly load CSS/JS.
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	public function give_update_cmb_meta_box_url( $url ) {
		//Path to Give's CMB
		return GIVE_PLUGIN_URL . '/includes/libraries/cmb2';
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
		$tabs['general']  = esc_html__( 'General', 'give' );
		$tabs['gateways'] = esc_html__( 'Payment Gateways', 'give' );
		$tabs['display']  = esc_html__( 'Display Options', 'give' );
		$tabs['emails']   = esc_html__( 'Emails', 'give' );

		if ( ! empty( $settings['addons']['fields'] ) ) {
			$tabs['addons'] = esc_html__( 'Add-ons', 'give' );
		}

		if ( ! empty( $settings['licenses']['fields'] ) ) {
			$tabs['licenses'] = esc_html__( 'Licenses', 'give' );
		}

		$tabs['advanced']    = esc_html__( 'Advanced', 'give' );
		$tabs['api']         = esc_html__( 'API', 'give' );
		$tabs['system_info'] = esc_html__( 'System Info', 'give' );

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

			<h1 class="screen-reader-text"><?php echo get_admin_page_title(); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->give_get_settings_tabs() as $tab_id => $tab_name ) {

					$tab_url = esc_url( add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) ) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '" id="tab-' . $tab_id . '">' . esc_html( $tab_name ) . '</a>';

				}
				?>
			</h2>

			<?php cmb2_metabox_form( $this->give_settings( $active_tab ), $this->key ); ?>

		</div><!-- .wrap -->

		<?php
	}


	/**
	 *
	 * Modify CMB2 Default Form Output
	 *
	 * @param string @args
	 *
	 * @since 1.0
	 *
	 * @param $form_format
	 * @param $object_id
	 * @param $cmb
	 *
	 * @return string
	 */
	function give_modify_cmb2_form_output( $form_format, $object_id, $cmb ) {

		//only modify the give settings form
		if ( 'give_settings' == $object_id ) {

			return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="give_settings_saved" value="true"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="give-submit-wrap"><input type="submit" name="submit-cmb" value="' . esc_attr__( 'Save Settings', 'give' ) . '" class="button-primary"></div></form>';

		}

		return $form_format;

	}

	/**
	 * Define General Settings Metabox and field configurations.
	 *
	 * Filters are provided for each settings section to allow add-ons and other plugins to add their own settings
	 *
	 * @param $active_tab |string active tab settings; null returns full array
	 *
	 * @return array
	 */
	public function give_settings( $active_tab ) {

		$give_settings = array(
			/**
			 * General Settings
			 */
			'general'     => array(
				'id'         => 'general_settings',
				'give_title' => esc_html__( 'General Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_general', array(
						array(
							'name' => esc_html__( 'General Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_1'
						),
						array(
							'name'    => esc_html__( 'Success Page', 'give' ),
							/* translators: %s: [give_receipt] */
							'desc'    => sprintf( __( 'The page donors are sent to after completing their donations. The %s shortcode should be on this page.', 'give' ), '<code>[give_receipt]</code>' ),
							'id'      => 'success_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Failed Donation Page', 'give' ),
							'desc'    => esc_html__( 'The page donors are sent to if their donation is cancelled or fails.', 'give' ),
							'id'      => 'failure_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Donation History Page', 'give' ),
							/* translators: %s: [donation_history] */
							'desc'    => sprintf( __( 'The page showing a complete donation history for the current user. The %s shortcode should be on this page.', 'give' ), '<code>[donation_history]</code>' ),
							'id'      => 'history_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Base Country', 'give' ),
							'desc'    => esc_html__( 'The country your site operates from.', 'give' ),
							'id'      => 'base_country',
							'type'    => 'select',
							'options' => give_get_country_list(),
						),
						array(
							'name' => esc_html__( 'Currency Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_2'
						),
						array(
							'name'    => esc_html__( 'Currency', 'give' ),
							'desc'    => esc_html__( 'The donation currency. Note that some payment gateways have currency restrictions.', 'give' ),
							'id'      => 'currency',
							'type'    => 'select',
							'options' => give_get_currencies(),
							'default' => 'USD',
						),
						array(
							'name'    => esc_html__( 'Currency Position', 'give' ),
							'desc'    => esc_html__( 'The position of the currency symbol.', 'give' ),
							'id'      => 'currency_position',
							'type'    => 'select',
							'options' => array(
								/* translators: %s: currency symbol */
								'before' => sprintf( esc_html__( 'Before - %s10', 'give' ), give_currency_symbol( give_get_currency() ) ),
								/* translators: %s: currency symbol */
								'after'  => sprintf( esc_html__( 'After - 10%s', 'give' ), give_currency_symbol( give_get_currency() ) )
							),
							'default' => 'before',
						),
						array(
							'name'            => esc_html__( 'Thousands Separator', 'give' ),
							'desc'            => esc_html__( 'The symbol (usually , or .) to separate thousands.', 'give' ),
							'id'              => 'thousands_separator',
							'type'            => 'text_small',
							'sanitization_cb' => 'give_sanitize_thousand_separator',
							'default'         => ',',
						),
						array(
							'name'    => esc_html__( 'Decimal Separator', 'give' ),
							'desc'    => esc_html__( 'The symbol (usually , or .) to separate decimal points.', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text_small',
							'default' => '.',
						),
						array(
							'name'            => esc_html__( 'Number of Decimals', 'give' ),
							'desc'            => esc_html__( 'The number of decimal points displayed in amounts.', 'give' ),
							'id'              => 'number_decimals',
							'type'            => 'text_small',
							'default'         => 2,
							'sanitization_cb' => 'give_sanitize_number_decimals',
						),
					)
				)
			),
			/**
			 * Payment Gateways
			 */
			'gateways'    => array(
				'id'         => 'payment_gateways',
				'give_title' => esc_html__( 'Payment Gateways', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_gateways', array(
						array(
							'name' => esc_html__( 'Gateways Settings', 'give' ),
							'desc' => '',
							'id'   => 'give_title_gateway_settings_1',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Test Mode', 'give' ),
							'desc' => esc_html__( 'While in test mode no live donations are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'give' ),
							'id'   => 'test_mode',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Enabled Gateways', 'give' ),
							'desc' => esc_html__( 'Enable your payment gateway. Can be ordered by dragging.', 'give' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),
						array(
							'name' => esc_html__( 'Default Gateway', 'give' ),
							'desc' => esc_html__( 'The gateway that will be selected by default.', 'give' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway'
						),
						array(
							'name' => esc_html__( 'PayPal Standard', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_gateway_settings_2',
						),
						array(
							'name' => esc_html__( 'PayPal Email', 'give' ),
							'desc' => esc_html__( 'Enter your PayPal account\'s email.', 'give' ),
							'id'   => 'paypal_email',
							'type' => 'text_email',
						),
						array(
							'name' => esc_html__( 'PayPal Page Style', 'give' ),
							'desc' => esc_html__( 'Enter the name of the page style to use, or leave blank to use the default.', 'give' ),
							'id'   => 'paypal_page_style',
							'type' => 'text',
						),
						array(
							'name'    => esc_html__( 'PayPal Transaction Type', 'give' ),
							'desc'    => esc_html__( 'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, Give transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.', 'give' ),
							'id'      => 'paypal_button_type',
							'type'    => 'radio_inline',
							'options' => array(
								'donation' => esc_html__( 'Donation', 'give' ),
								'standard' => esc_html__( 'Standard Transaction', 'give' )
							),
							'default' => 'donation',
						),
						array(
							'name' => esc_html__( 'Disable PayPal IPN Verification', 'give' ),
							'desc' => esc_html__( 'If donations are not getting marked as complete, use a slightly less secure method of verifying donations.', 'give' ),
							'id'   => 'disable_paypal_verification',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Offline Donations', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_gateway_settings_3',
						),
						array(
							'name' => esc_html__( 'Collect Billing Details', 'give' ),
							'desc' => esc_html__( 'Enable to request billing details for offline donations. Will appear above offline donation instructions. Can be enabled/disabled per form.', 'give' ),
							'id'   => 'give_offline_donation_enable_billing_fields',
							'type' => 'checkbox'
						),
						array(
							'name'    => esc_html__( 'Offline Donation Instructions', 'give' ),
							'desc'    => esc_html__( 'The following content will appear for all forms when the user selects the offline donation payment option. Note: You may customize the content per form as needed.', 'give' ),
							'id'      => 'global_offline_donation_content',
							'default' => give_get_default_offline_donation_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
						array(
							'name'    => esc_html__( 'Offline Donation Email Instructions Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the donation receipt email.', 'give' ),
							'id'      => 'offline_donation_subject',
							'default' => esc_attr__( '{donation} - Offline Donation Instructions', 'give' ),
							'type'    => 'text'
						),
						array(
							'name'    => esc_html__( 'Offline Donation Email Instructions', 'give' ),
							'desc'    => esc_html__( 'Enter the instructions you want emailed to the donor after they have submitted the donation form. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
							'id'      => 'global_offline_donation_email',
							'default' => give_get_default_offline_donation_email_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						)
					)
				)
			),
			/** Display Settings */
			'display'     => array(
				'id'         => 'display_settings',
				'give_title' => esc_html__( 'Display Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_display', array(
						array(
							'name' => esc_html__( 'Display Settings', 'give' ),
							'desc' => '',
							'id'   => 'give_title_display_settings_1',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Disable CSS', 'give' ),
							'desc' => esc_html__( 'Enable this option if you would like to disable all of Give\'s included CSS stylesheets.', 'give' ),
							'id'   => 'disable_css',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Enable Floating Labels', 'give' ),
							/* translators: %s: http://docs.givewp.com/form-floating-labels */
							'desc' => sprintf( wp_kses( __( 'Enable <a href="%s" target="_blank">floating labels</a> in Give\'s donation forms. Note that if the "Disable CSS" option is enabled, you will need to style the floating labels yourself.', 'give' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( 'http://docs.givewp.com/form-floating-labels' ) ),
							'id'   => 'floatlabels',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Welcome Screen', 'give' ),
							/* translators: %s: about page URL */
							'desc' => sprintf( wp_kses( __( 'Enable this option if you would like to disable the <a href="%s" target="_blank">Give Welcome screen</a> every time Give is activated and/or updated.', 'give' ), array(
								'a' => array(
									'href'   => array(),
									'target' => array()
								)
							) ), esc_url( admin_url( 'index.php?page=give-about' ) ) ),
							'id'   => 'disable_welcome',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Post Types', 'give' ),
							'desc' => '',
							'id'   => 'give_title_display_settings_2',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Disable Form Single Views', 'give' ),
							'desc' => esc_html__( 'By default, all forms have single views enabled which create a specific URL on your website for that form. This option disables the singular and archive views from being publicly viewable. Note: you will need to embed forms using a shortcode or widget if enabled.', 'give' ),
							'id'   => 'disable_forms_singular',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Form Archives', 'give' ),
							'desc' => esc_html__( 'Archives pages list all the forms you have created. This option will disable only the form\'s archive page(s). The single form\'s view will remain in place. Note: you will need to refresh your permalinks after this option has been enabled.', 'give' ),
							'id'   => 'disable_forms_archives',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Form Excerpts', 'give' ),
							'desc' => esc_html__( 'The excerpt is an optional summary or description of a donation form; in short, a summary as to why the user should give.', 'give' ),
							'id'   => 'disable_forms_excerpt',
							'type' => 'checkbox'
						),
						array(
							'name'    => esc_html__( 'Featured Image Size', 'give' ),
							'desc'    => esc_html__( 'The Featured Image is an image that is chosen as the representative image for a donation form. Some themes may have custom featured image sizes. Please select the size you would like to display for your single donation form\'s featured image.', 'give' ),
							'id'      => 'featured_image_size',
							'type'    => 'select',
							'default' => 'large',
							'options' => give_get_featured_image_sizes()
						),
						array(
							'name' => esc_html__( 'Disable Form Featured Image', 'give' ),
							'desc' => esc_html__( 'If you do not wish to use the featured image functionality you can disable it using this option and it will not be displayed for single donation forms.', 'give' ),
							'id'   => 'disable_form_featured_img',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Single Form Sidebar', 'give' ),
							'desc' => esc_html__( 'The sidebar allows you to add additional widget to the Give single form view. If you don\'t plan on using the sidebar you may disable it with this option.', 'give' ),
							'id'   => 'disable_form_sidebar',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Taxonomies', 'give' ),
							'desc' => '',
							'id'   => 'give_title_display_settings_3',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Enable Form Categories', 'give' ),
							'desc' => esc_html__( 'Enables the "Category" taxonomy for all Give forms.', 'give' ),
							'id'   => 'categories',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Enable Form Tags', 'give' ),
							'desc' => esc_html__( 'Enables the "Tag" taxonomy for all Give forms.', 'give' ),
							'id'   => 'tags',
							'type' => 'checkbox'
						),
					)
				)

			),
			/**
			 * Emails Options
			 */
			'emails'      => array(
				'id'         => 'email_settings',
				'give_title' => esc_html__( 'Email Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_emails', array(
						array(
							'name' => esc_html__( 'Email Settings', 'give' ),
							'desc' => '',
							'id'   => 'give_title_email_settings_1',
							'type' => 'give_title'
						),
						array(
							'id'      => 'email_template',
							'name'    => esc_html__( 'Email Template', 'give' ),
							'desc'    => esc_html__( 'Choose a template. Click "Save Changes" then "Preview Donation Receipt" to see the new template.', 'give' ),
							'type'    => 'select',
							'options' => give_get_email_templates()
						),
						array(
							'id'   => 'email_logo',
							'name' => esc_html__( 'Logo', 'give' ),
							'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'give' ),
							'type' => 'file'
						),
						array(
							'id'      => 'from_name',
							'name'    => esc_html__( 'From Name', 'give' ),
							'desc'    => esc_html__( 'The name that appears in the "From" field in donation receipt emails.', 'give' ),
							'default' => get_bloginfo( 'name' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'from_email',
							'name'    => esc_html__( 'From Email', 'give' ),
							'desc'    => esc_html__( 'Email to send donation receipts from. This will act as the "from" and "reply-to" address.', 'give' ),
							'default' => get_bloginfo( 'admin_email' ),
							'type'    => 'text'
						),
						array(
							'name' => esc_html__( 'Donation Receipt', 'give' ),
							'desc' => '',
							'id'   => 'give_title_email_settings_2',
							'type' => 'give_title'
						),
						array(
							'id'      => 'donation_subject',
							'name'    => esc_html__( 'Donation Email Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the donation receipt email.', 'give' ),
							'default' => esc_attr__( 'Donation Receipt', 'give' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'donation_receipt',
							'name'    => esc_html__( 'Donation Receipt', 'give' ),
							'desc'    => sprintf(
							/* translators: %s: emails tags list */
								esc_html__( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted. Available template tags: %s', 'give' ),
								'<br/>' . give_get_emails_tags_list()
							),
							'type'    => 'wysiwyg',
							'default' => give_get_default_donation_receipt_email()
						),
						array(
							'name' => esc_html__( 'New Donation Notification', 'give' ),
							'desc' => '',
							'id'   => 'give_title_email_settings_3',
							'type' => 'give_title'
						),
						array(
							'id'      => 'donation_notification_subject',
							'name'    => esc_html__( 'Donation Notification Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the donation notification email.', 'give' ),
							'type'    => 'text',
							'default' => esc_attr__( 'New Donation - #{payment_id}', 'give' )
						),
						array(
							'id'      => 'donation_notification',
							'name'    => esc_html__( 'Donation Notification', 'give' ),
							'desc'    => sprintf(
							/* translators: %s: emails tags list */
								esc_html__( 'Enter the email that is sent to donation notification emails after completion of a donation. HTML is accepted. Available template tags: %s', 'give' ),
								'<br/>' . give_get_emails_tags_list()
							),
							'type'    => 'wysiwyg',
							'default' => give_get_default_donation_notification_email()
						),
						array(
							'id'      => 'admin_notice_emails',
							'name'    => esc_html__( 'Donation Notification Emails', 'give' ),
							'desc'    => __( 'Enter the email address(es) that should receive a notification anytime a donation is made, please only enter <span class="give-underline">one email address per line</span> and <strong>not separated by commas</strong>.', 'give' ),
							'type'    => 'textarea',
							'default' => get_bloginfo( 'admin_email' )
						),
						array(
							'id'   => 'disable_admin_notices',
							'name' => esc_html__( 'Disable Admin Notifications', 'give' ),
							'desc' => esc_html__( 'Check this box if you do not want to receive emails when new donations are made.', 'give' ),
							'type' => 'checkbox'
						)
					)
				)
			),
			/** Extension Settings */
			'addons'      => array(
				'id'         => 'addons',
				'give_title' => esc_html__( 'Give Add-ons Settings', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_addons', array()
				)
			),
			/** Licenses Settings */
			'licenses'    => array(
				'id'         => 'licenses',
				'give_title' => esc_html__( 'Give Licenses', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_licenses', array()
				)
			),
			/** Advanced Options */
			'advanced'    => array(
				'id'         => 'advanced_options',
				'give_title' => esc_html__( 'Advanced Options', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_advanced', array(
						array(
							'name' => esc_html__( 'Access Control', 'give' ),
							'desc' => '',
							'id'   => 'give_title_session_control_1',
							'type' => 'give_title'
						),
						array(
							'id'      => 'session_lifetime',
							'name'    => esc_html__( 'Session Lifetime', 'give' ),
							'desc'    => esc_html__( 'The length of time a user\'s session is kept alive. Give starts a new session per user upon donation. Sessions allow donors to view their donation receipts without being logged in.', 'give' ),
							'type'    => 'select',
							'options' => array(
								'86400'  => esc_html__( '24 Hours', 'give' ),
								'172800' => esc_html__( '48 Hours', 'give' ),
								'259200' => esc_html__( '72 Hours', 'give' ),
								'604800' => esc_html__( '1 Week', 'give' ),
							)
						),
						array(
							'name' => esc_html__( 'Email Access', 'give' ),
							'desc' => esc_html__( 'Would you like your donors to be able to access their donation history using only email? Donors whose sessions have expired and do not have an account may still access their donation history via a temporary email access link.', 'give' ),
							'id'   => 'email_access',
							'type' => 'checkbox',
						),
						array(
							'id'      => 'recaptcha_key',
							'name'    => esc_html__( 'reCAPTCHA Site Key', 'give' ),
							/* translators: %s: https://www.google.com/recaptcha/ */
							'desc'    => sprintf( __( 'If you would like to prevent spam on the email access form navigate to <a href="%s" target="_blank">the reCAPTCHA website</a> and sign up for an API key. The reCAPTCHA uses Google\'s user-friendly single click verification method.', 'give' ), esc_url( 'https://www.google.com/recaptcha/' ) ),
							'default' => '',
							'type'    => 'text'
						),
						array(
							'id'      => 'recaptcha_secret',
							'name'    => esc_html__( 'reCAPTCHA Secret Key', 'give' ),
							'desc'    => esc_html__( 'Please paste the reCAPTCHA secret key here from your manage reCAPTCHA API Keys panel.', 'give' ),
							'default' => '',
							'type'    => 'text'
						),
						array(
							'name' => esc_html__( 'Data Control', 'give' ),
							'desc' => '',
							'id'   => 'give_title_data_control_2',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Remove All Data on Uninstall?', 'give' ),
							'desc' => esc_html__( 'When the plugin is deleted, completely remove all Give data.', 'give' ),
							'id'   => 'uninstall_on_delete',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Filter Control', 'give' ),
							'desc' => '',
							'id'   => 'give_title_filter_control',
							'type' => 'give_title'
						),
						array(
							/* translators: %s: the_content */
							'name' => sprintf( __( 'Disable %s filter', 'give' ), '<code>the_content</code>' ),
							/* translators: 1: https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content 2: the_content */
							'desc' => sprintf( __( 'If you are seeing extra social buttons, related posts, or other unwanted elements appearing within your forms then you can disable WordPress\' content filter. <a href="%1$s" target="_blank">Learn more</a> about %2$s filter.', 'give' ), esc_url( 'https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content' ), '<code>the_content</code>' ),
							'id'   => 'disable_the_content_filter',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Script Loading', 'give' ),
							'desc' => '',
							'id'   => 'give_title_script_control',
							'type' => 'give_title'
						),
						array(
							'name' => esc_html__( 'Load Scripts in Footer?', 'give' ),
							'desc' => esc_html__( 'Check this box if you would like Give to load all frontend JavaScript files in the footer.', 'give' ),
							'id'   => 'scripts_footer',
							'type' => 'checkbox'
						)
					)
				)
			),
			/** API Settings */
			'api'         => array(
				'id'         => 'api',
				'give_title' => esc_html__( 'API', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'show_names' => false, // Hide field names on the left
				'fields'     => apply_filters( 'give_settings_system', array(
						array(
							'id'   => 'api',
							'name' => esc_html__( 'API', 'give' ),
							'type' => 'api'
						)
					)
				)
			),
			/** Licenses Settings */
			'system_info' => array(
				'id'         => 'system_info',
				'give_title' => esc_html__( 'System Info', 'give' ),
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'fields'     => apply_filters( 'give_settings_system', array(
						array(
							'id'   => 'system-info-textarea',
							'name' => esc_html__( 'System Info', 'give' ),
							'desc' => esc_html__( 'Please copy and paste this information in your ticket when contacting support.', 'give' ),
							'type' => 'system_info'
						)
					)
				)
			),
		);

		$give_settings = apply_filters( 'give_registered_settings', $give_settings );

		//Return all settings array if no active tab
		if (  empty( $active_tab ) || ! isset( $give_settings[ $active_tab ] ) ) {
			return $give_settings;
		}


		// Add other tabs and settings fields as needed
		return $give_settings[ $active_tab ];

	}

	/**
	 * Show Settings Notices
	 */
	public function settings_notices() {

		if ( ! isset( $_POST['give_settings_saved'] ) ) {
			return;
		}

		add_settings_error( 'give-notices', 'global-settings-updated', esc_html__( 'Settings updated.', 'give' ), 'updated' );

	}


	/**
	 * Public getter method for retrieving protected/private variables
	 *
	 * @since  1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed         Field value or exception is thrown.
	 * @throws Exception     Throws an exception if the field is invalid.
	 */
	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'fields', 'give_title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		if ( 'option_metabox' === $field ) {
			return $this->option_metabox();
		}

		throw new Exception( sprintf( esc_html__( 'Invalid property: %s', 'give' ), $field ) );
	}


}

// Get it started
$Give_Settings = new Give_Plugin_Settings();

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 *
 * @param  string $key     Options array key
 * @param  string $default The default option if the option isn't set
 *
 * @return mixed        Option value
 */
function give_get_option( $key = '', $default = false ) {
	$give_options = give_get_settings();
	$value        = ! empty( $give_options[ $key ] ) ? $give_options[ $key ] : $default;
	$value        = apply_filters( 'give_get_option', $value, $key, $default );

	return apply_filters( "give_get_option_{$key}", $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an give setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the give_options array.
 *
 * @since 1.0
 *
 * @param string          $key   The Key to update
 * @param string|bool|int $value The value to set the key to
 *
 * @return boolean True if updated, false if not.
 */
function give_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = give_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'give_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'give_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'give_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $give_options;
		$give_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an give setting value in both the db and the global variable.
 *
 * @since 1.0
 *
 * @global       $give_options
 *
 * @param string $key The Key to delete
 *
 * @return boolean True if updated, false if not.
 */
function give_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'give_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	$did_update = update_option( 'give_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $give_options;
		$give_options = $options;
	}

	return $did_update;
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
 * Give Settings Array Insert.
 *
 * Allows other Add-ons and plugins to insert Give settings at a desired position.
 *
 * @since      1.3.5
 *
 * @param $array
 * @param $position |int|string Expects an array key or 'id' of the settings field to appear after
 * @param $insert   |array a valid array of options to insert
 *
 * @return array
 */
function give_settings_array_insert( $array, $position, $insert ) {
	if ( is_int( $position ) ) {
		array_splice( $array, $position, 0, $insert );
	} else {

		foreach ( $array as $index => $subarray ) {
			if ( isset( $subarray['id'] ) && $subarray['id'] == $position ) {
				$pos = $index;
			}
		}

		if ( ! isset( $pos ) ) {
			return $array;
		}

		$array = array_merge(
			array_slice( $array, 0, $pos ),
			$insert,
			array_slice( $array, $pos )
		);
	}

	return $array;
}


/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $field_arr
 * @param array $saved_values
 * @return void
 */
function give_enabled_gateways_callback( $field_arr, $saved_values = array() ) {

	$id       = $field_arr['id'];
	$gateways = give_get_ordered_payment_gateways( give_get_payment_gateways() );

	echo '<ul class="give-checklist-fields give-payment-gatways-list">';

	foreach ( $gateways as $key => $option ) :

		if ( is_array( $saved_values ) && array_key_exists( $key, $saved_values ) ) {
			$enabled = '1';
		} else {
			$enabled = null;
		}

		echo '<li><span class="give-drag-handle"><span class="dashicons dashicons-menu"></span></span><input name="' . $id . '[' . $key . ']" id="' . $id . '[' . $key . ']" type="checkbox" value="1" ' . checked( '1', $enabled, false ) . '/>&nbsp;';
		echo '<label for="' . $id . '[' . $key . ']">' . $option['admin_label'] . '</label></li>';

	endforeach;

	echo '</ul>';
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since  1.0
 * @param  array $field_arr
 * @param  array $saved_value
 * @return void
 */
function give_default_gateway_callback( $field_arr, $saved_value ) {
	$id                = $field_arr['id'];
	$gateways          = give_get_enabled_payment_gateways();
	$saved_value       = give_get_default_gateway( null );

	echo '<select class="give-select" name="' . $id . '" id="' . $id . '">';

		foreach ( $gateways as $key => $option ) :
			$selected = isset( $saved_value ) ? selected( $key, $saved_value, false ) : '';
			echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
		endforeach;

	echo '</select>';

}

/**
 * Give Title
 *
 * Renders custom section titles output; Really only an  because CMB2's output is a bit funky
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

	echo '<hr>' . $field_description;

}

/**
 * Give Description
 *
 * Renders custom description text which any plugin can use to output content, html, php, etc.
 *
 * @since      1.3.5
 *
 * @param       $field_object , $escaped_value, $object_id, $object_type, $field_type_object
 *
 * @return void
 */
function give_description_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$id                = $field_type_object->field->args['id'];
	$title             = $field_type_object->field->args['name'];
	$field_description = $field_type_object->field->args['desc'];


	echo $field_description;

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
 * Featured Image Sizes
 *
 * Outputs an array for the "Featured Image Size" option found under Settings > Display Options.
 *
 * @since 1.4
 *
 * @global $_wp_additional_image_sizes
 */
function give_get_featured_image_sizes() {
	global $_wp_additional_image_sizes;
	$sizes     = array();
	$get_sizes = get_intermediate_image_sizes();

	// check whether intermediate image sizes exist first
	if ( empty( $get_sizes ) ) {
		$get_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );
	}

	foreach ( $get_sizes as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
			$sizes[ $_size ] = $_size . ' - ' . get_option( "{$_size}_size_w" ) . 'x' . get_option( "{$_size}_size_h" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = $_size . ' - ' . $_wp_additional_image_sizes[ $_size ]['width'] . 'x' . $_wp_additional_image_sizes[ $_size ]['height'];
		}

	}

	return apply_filters( 'give_get_featured_image_sizes', $sizes );
}


/**
 * Give License Key Callback
 *
 * Registers the license field callback for EDD's Software Licensing.
 *
 * @since       1.0
 *
 * @param array $field_object , $escaped_value, $object_id, $object_type, $field_type_object Arguments passed by CMB2
 *
 * @return void
 */
function give_license_key_callback( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
	/* @var CMB2_Types $field_type_object */

    $id                   = $field_type_object->field->args['id'];
	$field_description    = $field_type_object->field->args['desc'];
	$license              = $field_type_object->field->args['options']['license'];
    $license_key          = $escaped_value;
    $is_license_key       = apply_filters( 'give_is_license_key', ( is_object( $license ) && ! empty( $license ) ) );
    $is_valid_license     = apply_filters( 'give_is_valid_license', ( $is_license_key && property_exists( $license, 'license' ) && 'valid' === $license->license ) );
    $shortname            = $field_type_object->field->args['options']['shortname'];
	$field_classes        = 'regular-text give-license-field';
	$type                 = empty( $escaped_value ) || ! $is_valid_license ? 'text' : 'password';
    $custom_html          = '';
    $messages             = array();
    $class                = '';
    $account_page_link    = $field_type_object->field->args['options']['account_url'];
    $checkout_page_link   = $field_type_object->field->args['options']['checkout_url'];
    $addon_name           = $field_type_object->field->args['options']['item_name'];
    $license_status       = null;
    $is_in_subscription   = null;

	// By default query on edd api url will return license object which contain status and message property, this can break below functionality.
	// To combat that check if status is set to error or not, if yes then set $is_license_key to false.
	if ( $is_license_key && property_exists( $license, 'status' ) && 'error' === $license->status ) {
		$is_license_key = false;
	}


	// Check if current license is part of subscription or not.
	$subscriptions = get_option( 'give_subscriptions' );

	if ( $is_license_key && $subscriptions ) {
		foreach ( $subscriptions as $subscription ) {
			if ( in_array( $license_key, $subscription['licenses'] ) ) {
				$is_in_subscription = $subscription['id'];
				break;
			}
		}
	}


	if ( $is_license_key ) {
		if ( $is_in_subscription ) {
			$subscription_expires = strtotime( $subscriptions[ $is_in_subscription ]['expires'] );
			$subscription_status  = esc_html__( 'renew', 'give' );

			if ( ( 'active' !== $subscriptions[ $is_in_subscription ]['status'] ) ) {
				$subscription_status = esc_html__( 'expire', 'give' );
			}

			if ( $subscription_expires < current_time( 'timestamp', 1 ) ) {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%s" target="_blank">#%d</a>) expired. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$checkout_page_link . '?edd_license_key=' . $subscriptions[ $is_in_subscription ]['license_key'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
				);
				$license_status = 'license-expired';
			} elseif ( strtotime( '- 7 days', $subscription_expires ) < current_time( 'timestamp', 1 ) ) {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%s" target="_blank">#%d</a>) will %s in %s.', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$subscription_status,
					human_time_diff( current_time( 'timestamp', 1 ), strtotime( $subscriptions[ $is_in_subscription ]['expires'] ) )
				);
				$license_status = 'license-expires-soon';
			} else {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%s" target="_blank">#%d</a>) will %s on %s.', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$subscription_status,
					date_i18n( get_option( 'date_format' ), strtotime( $subscriptions[ $is_in_subscription ]['expires'], current_time( 'timestamp' ) ) )
				);
				$license_status = 'license-expiration-date';
			}


		} elseif ( empty( $license->success ) && property_exists( $license, 'error' ) ) {

            // activate_license 'invalid' on anything other than valid, so if there was an error capture it
            switch(   $license->error ) {
                case 'expired' :
                    $class = $license->error;
                    $messages[] = sprintf(
                        __( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'give' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                        $checkout_page_link . '?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
                    );
                    $license_status = 'license-' . $class;
                    break;

				case 'missing' :
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'give' ),
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					);
					$license_status = 'license-' . $class;
					break;

				case 'invalid' :
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'give' ),
						$addon_name,
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);
					$license_status = 'license-' . $class;
					break;

				case 'site_inactive' :
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'give' ),
						$addon_name,
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);
					$license_status = 'license-' . $class;
					break;

                case 'item_name_mismatch' :
                    $class = $license->error;
                    $messages[] = sprintf( __( 'This license %s does not belong to %s.', 'give' ), $license_key, $addon_name );
                    $license_status = 'license-' . $class;
                    break;

				case 'no_activations_left':
					$class          = $license->error;
					$messages[]     = sprintf( __( 'Your license key has reached it\'s activation limit. <a href="%s">View possible upgrades</a> now.', 'give' ), $account_page_link );
					$license_status = 'license-' . $class;
					break;
			}
		} else {
			switch ( $license->license ) {
				case 'valid' :
				default:
					$class      = 'valid';
					$now        = current_time( 'timestamp' );
					$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

					if ( 'lifetime' === $license->expires ) {
						$messages[]     = esc_html__( 'License key never expires.', 'give' );
						$license_status = 'license-lifetime-notice';
					} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
						$messages[]     = sprintf(
							__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'give' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							$checkout_page_link . '?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
						);
						$license_status = 'license-expires-soon';
					} else {
						$messages[]     = sprintf(
							__( 'Your license key expires on %s.', 'give' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
						);
						$license_status = 'license-expiration-date';
					}
					break;
			}
		}
	} else {
		$messages[]     = sprintf(
			__( 'To receive updates, please enter your valid %s license key.', 'give' ),
			$addon_name
		);
		$license_status = 'inactive';
	}


	// Add class for input field if license is active.
	if ( $is_valid_license ) {
		$field_classes .= ' give-license-active';
	}

	// Get input field html.
	$input_field_html = "<input type=\"{$type}\" name=\"{$id}\" class=\"{$field_classes}\" value=\"{$license_key}\">";

	// If license is active so show deactivate button.
	if ( $is_valid_license ) {
        // Get input field html.
		$input_field_html = "<input type=\"{$type}\" name=\"{$id}\" class=\"{$field_classes}\" value=\"{$license_key}\" readonly=\"readonly\">";

		$custom_html = '<input type="submit" class="button button-small give-license-deactivate" name="' . $id . '_deactivate" value="' . esc_attr__( 'Deactivate License', 'give' ) . '"/>';


	}

	// Field description.
	$custom_html .= '<label for="give_settings[' . $id . ']"> ' . $field_description . '</label>';

	// If no messages found then inform user that to get updated in future register yourself.
	if ( empty( $messages ) ) {
		$messages[] = apply_filters( "{$shortname}_default_addon_notice", esc_html__( 'To receive updates, please enter your valid license key.', 'give' ) );
	}

    foreach( $messages as $message ) {
        $custom_html .= '<div class="give-license-status-notice give-' . $license_status . '">';
        $custom_html .= '<p>' . $message . '</p>';
        $custom_html .= '</div>';
    }


	// Field html.
	$custom_html = apply_filters( 'give_license_key_field_html', $input_field_html . $custom_html, $field_type_object );

	// Nonce.
	wp_nonce_field( $id . '-nonce', $id . '-nonce' );

    // Print field html.
    echo "<div class=\"give-license-key\"><label for=\"{$id}\">{$addon_name }</label></div><div class=\"give-license-block\">{$custom_html}</div>";
}


/**
 * Display the API Keys
 *
 * @since       1.0
 * @return      void
 */
function give_api_callback() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	/**
	 * Fires before displaying API keys.
	 *
	 * @since 1.0
	 */
	do_action( 'give_tools_api_keys_before' );

	require_once GIVE_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';

	$api_keys_table = new Give_API_Keys_Table();
	$api_keys_table->prepare_items();
	$api_keys_table->display();
	?>
	<span class="cmb2-metabox-description api-description">
		<?php echo sprintf(
		/* translators: 1: http://docs.givewp.com/api 2: http://docs.givewp.com/addon-zapier */
			__( 'You can create API keys for individual users within their profile edit screen. API keys allow users to use the <a href="%1$s" target="_blank">Give REST API</a> to retrieve donation data in JSON or XML for external applications or devices, such as <a href="%2$s" target="_blank">Zapier</a>.', 'give' ),
			esc_url( 'http://docs.givewp.com/api' ),
			esc_url( 'http://docs.givewp.com/addon-zapier' )
		); ?>
	</span>
	<?php

	/**
	 * Fires after displaying API keys.
	 *
	 * @since 1.0
	 */
	do_action( 'give_tools_api_keys_after' );
}

add_action( 'give_settings_tab_api_keys', 'give_api_callback' );

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function give_hook_callback( $args ) {

	$id = $args['id'];

	/**
	 * Fires in give field.
	 *
	 * @since 1.0
	 */
	do_action( "give_{$id}" );

}


/**
 * Check if radio(enabled/disabled) and checkbox(on) is active or not.
 *
 * @since  1.8
 * @param  string $value
 * @param  string $compare_with
 * @return bool
 */
function give_is_setting_enabled( $value, $compare_with = null ) {
	if( ! is_null( $compare_with ) ) {

		if( is_array( $compare_with ) ) {
			// Output.
			return in_array( $value, $compare_with );
		}

		// Output.
		return ( $value === $compare_with );
	}

	// Backward compatibility: From version 1.8 most of setting is modified to enabled/disabled
	// Output.
	return ( in_array( $value, array( 'enabled', 'on', 'yes' ) ) ? true : false );
}