<?php
/**
 * Give Form Widget
 *
 * @package     GiveWP
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
use Give\Helpers\Form\Utils as FormUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give Form widget
 *
 * @since 1.0
 */
class Give_Forms_Widget extends WP_Widget {
	/**
	 * Script handle name.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $scriptHandle = 'give-admin-widgets-scripts';

	/**
	 * Widget identifier.
	 *
	 * We will use this to assign unique id to widget setting container and to generate unique inline script.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $widgetIdentifier = '';

	/**
	 * Widget id prefix.
	 *
	 * We will use this to assign unique id to widget setting container.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $widgetIdPrefix = 'give_forms_widget_container-';

	/**
	 * The widget class name
	 *
	 * @var string
	 */
	protected $self;

	/**
	 * Instantiate the class
	 */
	public function __construct() {
		$this->self = get_class( $this );
		parent::__construct(
			strtolower( $this->self ),
			esc_html__( 'GiveWP - Donation Form', 'give' ),
			[
				'description' => esc_html__( 'Display a GiveWP Donation Form in your theme\'s widget powered sidebar.', 'give' ),
			]
		);

		add_action( 'widgets_init', [ $this, 'widget_init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_widget_scripts' ] );
	}

	/**
	 * Load widget assets only on the widget page
	 *
	 * @return void
	 */
	public function admin_widget_scripts() {
		global $pagenow;

		// Load script only on widgets.php page.
		if ( ! in_array( $pagenow, [ 'widgets.php', 'customize.php' ] ) ) {
			return;
		}

		// Directories of assets.
		$js_dir = GIVE_PLUGIN_URL . 'assets/dist/';

		wp_enqueue_script( "{$this->scriptHandle}-js", $js_dir . 'js/admin-widgets.js', [ 'give-admin-scripts' ], GIVE_VERSION, false );
		wp_enqueue_style( "{$this->scriptHandle}-css", $js_dir . 'css/admin-widgets.css', [], GIVE_VERSION, false );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args     Display arguments including before_title, after_title,
	 *                        before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		// Exit do not have valid for id.
		if ( ! array_key_exists( 'id', $instance ) || empty( $instance['id'] ) ) {
			return;
		}

		$form_id      = (int) $instance['id'];
		$isLegacyForm = FormUtils::isLegacyForm( $form_id );

		echo $args['before_widget']; // XSS ok.

		/**
		 * Fires before widget settings form in the admin area.
		 *
		 * @param integer $form_id Form ID.
		 *
		 * @since 1.0
		 */
		do_action( 'give_before_forms_widget', $form_id );

		echo $title ? $args['before_title'] . $title . $args['after_title'] : ''; // XSS ok.

		// Use alias setting to set display setting when form template other then Legacy.
		if ( ! $isLegacyForm ) {
			$instance['display_style']         = $instance['tmp_display_style'];
			$instance['continue_button_title'] = $instance['tmp_continue_button_title'];

			unset( $instance['tmp_display_style'], $instance['tmp_continue_button_title'] );

			if ( 'button' === $instance['display_style'] && ! empty( $instance['introduction_text'] ) ) {
				printf(
					'<p>%1$s</p>',
					$instance['introduction_text']
				);
			}
		}

		echo give_form_shortcode( $instance );

		echo $args['after_widget']; // XSS ok.

		/**
		 * Fires after widget settings form in the admin area.
		 *
		 * @param integer $form_id Form ID.
		 *
		 * @since 1.0
		 */
		do_action( 'give_after_forms_widget', $form_id );
	}

	/**
	 * Output the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$defaults = [
			'title'                     => '',
			'id'                        => 0,
			'float_labels'              => 'global',
			'display_style'             => 'modal',
			'show_content'              => 'none',
			'continue_button_title'     => __( 'Continue', 'give' ),
			'introduction_text'         => __( 'Help our organization by donating today! All donations go directly to making a difference for our cause.', 'give' ),
			'button_text'               => __( 'Donate Now', 'give' ),
			'button_color'              => '#28C77B',

			// These settings are aliases for shortcode setting which prevent conflict when saving and showing setting. Later we will use them to set original settings.
			'tmp_display_style'         => 'button',
			'tmp_continue_button_title' => __( 'Continue', 'give' ),
		];

		$instance = wp_parse_args( (array) $instance, $defaults );

		// Backward compatibility: Set float labels as default if, it was set as empty previous.
		$instance['float_labels'] = empty( $instance['float_labels'] ) ? 'global' : $instance['float_labels'];

		$this->getScriptForBuilders();
		?>
		<div id="<?php echo $this->widgetIdPrefix . $this->widgetIdentifier; ?>" class="give_forms_widget_container">

			<?php // Widget: widget Title. ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'give' ); ?></label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
				<small class="give-field-description"><?php esc_html_e( 'Leave blank to hide the widget title.', 'give' ); ?></small>
			</p>

			<?php // Widget: Give Form. ?>
			<p class="donation-form give-hidden">
				<?php
				$selectFieldName = esc_attr( $this->get_field_name( 'id' ) );
				$selectFieldId   = esc_attr( sanitize_key( str_replace( '-', '_', esc_attr( $this->get_field_id( 'id' ) ) ) ) );
				printf(
					'<label for="%1$s">%2$s</label>',
					$selectFieldId,
					esc_html__( 'Donation Form:', 'give' )
				);

				echo Give()->html->forms_dropdown(
					[
						'selected'    => $instance['id'] ?: false,
						'id'          => $selectFieldId,
						'name'        => $selectFieldName,
						'placeholder' => esc_attr__( '- Select -', 'give' ),
						'query_args'  => [
							'post_status' => 'publish',
						],
						'select_atts' => 'style="width: 100%"',
					]
				);
				?>
				<small class="give-field-description"><?php esc_html_e( 'Select a donation form to use for this widget.', 'give' ); ?></small>
			</p>

			<div class="js-legacy-form-template-settings js-form-template-settings give-hidden">
				<legend class="screen-reader-text"><?php _e( 'Options for Legacy form template ', 'give' ); ?></legend>
				<?php // Widget: Display Style. ?>
				<p class="give_forms_display_style_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>"><?php esc_html_e( 'Display Style:', 'give' ); ?></label>
					<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-onpage"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-onpage" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="onpage" <?php checked( $instance['display_style'], 'onpage' ); ?>> <?php echo esc_html__( 'All Fields', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-reveal"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-reveal" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="reveal" <?php checked( $instance['display_style'], 'reveal' ); ?>> <?php echo esc_html__( 'Reveal', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-modal"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-modal" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="modal" <?php checked( $instance['display_style'], 'modal' ); ?>> <?php echo esc_html__( 'Modal', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-button"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-button" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="button" <?php checked( $instance['display_style'], 'button' ); ?>> <?php echo esc_html__( 'Button', 'give' ); ?></label>
					<small class="give-field-description"><?php echo esc_html__( 'Select a donation form style.', 'give' ); ?></small>
				</p>

				<?php // Widget: Continue Button Title. ?>
				<p class="give_forms_continue_button_title_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'continue_button_title' ) ); ?>"><?php esc_html_e( 'Button Text:', 'give' ); ?></label>
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'continue_button_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'continue_button_title' ) ); ?>" value="<?php echo esc_attr( $instance['continue_button_title'] ); ?>" />
					<small class="give-field-description"><?php esc_html_e( 'The button label for displaying the additional payment fields.', 'give' ); ?></small>
				</p>

				<?php // Widget: Floating Labels. ?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>"><?php esc_html_e( 'Floating Labels (optional):', 'give' ); ?></label>
					<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-global"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-global" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="global" <?php checked( $instance['float_labels'], 'global' ); ?>> <?php echo esc_html__( 'Global Option', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-enabled"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-enabled" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="enabled" <?php checked( $instance['float_labels'], 'enabled' ); ?>> <?php echo esc_html__( 'Enabled', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-disabled"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-disabled" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="disabled" <?php checked( $instance['float_labels'], 'disabled' ); ?>> <?php echo esc_html__( 'Disabled', 'give' ); ?></label>
					<small class="give-field-description">
						<?php
						printf(
							/* translators: %s: Documentation link to http://docs.givewp.com/form-floating-labels */
							__( 'Override the <a href="%s" target="_blank">floating labels</a> setting for this GiveWP form.', 'give' ),
							esc_url( 'http://docs.givewp.com/form-floating-labels' )
						);
						?>
					</small>
				</p>

				<?php // Widget: Display Content. ?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"><?php esc_html_e( 'Display Content (optional):', 'give' ); ?></label>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-none"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-none" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="none" <?php checked( $instance['show_content'], 'none' ); ?>> <?php echo esc_html__( 'None', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-above"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-above" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="above" <?php checked( $instance['show_content'], 'above' ); ?>> <?php echo esc_html__( 'Above', 'give' ); ?></label>
					&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-below"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-below" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="below" <?php checked( $instance['show_content'], 'below' ); ?>> <?php echo esc_html__( 'Below', 'give' ); ?></label>
					<small class="give-field-description"><?php esc_html_e( 'Override the display content setting for this GiveWP form.', 'give' ); ?></small>
				</p>
			</div>

			<div class="js-new-form-template-settings js-form-template-settings give-hidden">
				<legend class="screen-reader-text"><?php _e( 'Options for Legacy form template ', 'give' ); ?></legend>

				<?php // Widget: Display Style. ?>
				<p class="give_forms_display_style_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'tmp_display_style' ) ); ?>"><?php esc_html_e( 'Display Style:', 'give' ); ?></label>
					<label for="<?php echo esc_attr( $this->get_field_id( 'tmp_display_style' ) ); ?>-button"><span><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tmp_display_style' ) ); ?>-button" name="<?php echo esc_attr( $this->get_field_name( 'tmp_display_style' ) ); ?>" value="button" <?php checked( $instance['tmp_display_style'], 'button' ); ?>></span><span><?php echo esc_html__( 'Display a button and launch the donation form on click', 'give' ); ?></span></label>
					<label for="<?php echo esc_attr( $this->get_field_id( 'tmp_display_style' ) ); ?>-onpage"><span><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tmp_display_style' ) ); ?>-onpage" name="<?php echo esc_attr( $this->get_field_name( 'tmp_display_style' ) ); ?>" value="onpage" <?php checked( $instance['tmp_display_style'], 'onpage' ); ?>></span><span><?php echo esc_html__( 'Display the entire donation form in the sidebar', 'give' ); ?></span></label>
					<small class="give-field-description"><?php echo esc_html__( 'Select a donation form style.', 'give' ); ?></small>
				</p>

				<?php // Widget: Introduction Text. ?>
				<p class="give_forms_introduction_text_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'introduction_text' ) ); ?>"><?php esc_html_e( 'Widget Text:', 'give' ); ?></label>
					<textarea id="<?php echo esc_attr( $this->get_field_id( 'introduction_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'introduction_text' ) ); ?>" class="widefat"><?php echo esc_textarea( $instance['introduction_text'] ); ?></textarea>
					<small class="give-field-description"><?php esc_html_e( 'Provide an introduction text to invite the visitor to become a donor. Leave this blank to not display any text.', 'give' ); ?></small>
				</p>

				<?php // Widget: Continue Button Text. ?>
				<p class="give_forms_button_text_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'tmp_continue_button_title' ) ); ?>"><?php esc_html_e( 'Button Text:', 'give' ); ?></label>
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tmp_continue_button_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tmp_continue_button_title' ) ); ?>" value="<?php echo esc_attr( $instance['tmp_continue_button_title'] ); ?>" />
					<small class="give-field-description"><?php esc_html_e( 'This label will appear on button.', 'give' ); ?></small>
				</p>

				<?php // Widget: Continue Button Color. ?>
				<p class="give_forms_button_color_setting_row">
					<label for="<?php echo esc_attr( $this->get_field_id( 'button_color' ) ); ?>"><?php esc_html_e( 'Button Color:', 'give' ); ?></label>
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_color' ) ); ?>" value="<?php echo esc_attr( $instance['button_color'] ); ?>" />
					<small class="give-field-description"><?php esc_html_e( 'Set the color of button.', 'give' ); ?></small>
				</p>
			</div>

			<div class="js-loader">
				<p><span class="give-spinner spinner is-show"></span>&nbsp;&nbsp;<i><?php _e( 'Loading settings...', 'give' ); ?></i></p>
			</div>
			<?php wp_nonce_field( 'give-donation-form-widget', '_wpnonce', false ); ?>
		</div>
		<?php
	}

	/**
	 * Register the widget
	 *
	 * @return void
	 */
	public function widget_init() {
		register_widget( $this->self );
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();

		return $new_instance;
	}

	/**
	 * Flush widget cache
	 *
	 * @return void
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->self, 'widget' );
	}

	/**
	 * Get inline script for widget to add support for widget in page builder like Divi, Elementor and Beaver Builder.
	 *
	 * @since 2.7.0
	 */
	private function getScriptForBuilders() {
		global $pagenow;

		// Do not output inline script if admin widget script already printed.
		if ( wp_script_is( "{$this->scriptHandle}-js", 'done' ) || in_array( $pagenow, [ 'widgets.php', 'customize.php' ] ) ) {
			return;
		}

		$this->widgetIdentifier = uniqid();
		$containerId            = $this->widgetIdPrefix . $this->widgetIdentifier;
		?>
		<style>
			.give_forms_widget_container label:not([for*='display_style']):not([for*='float_labels']):not([for*='show_content']),
			p.give_forms_display_style_setting_row > label:first-child,
			.give_forms_widget_container label[for$='float_labels'],
			.give_forms_widget_container label[for$='show_content']{
				display: block;
				font-weight: 500;
			}

			p.give_forms_display_style_setting_row > label:first-child,
			.give_forms_widget_container label[for$='float_labels'],
			.give_forms_widget_container label[for$='show_content']{
				margin-bottom: 8px;
			}

			.give_forms_widget_container input {
				margin-left: 0 !important;
				margin-bottom: 0 !important;
			}

			.give_forms_widget_container input[type="radio"]{
				margin: 0! important;
			}

			.give_forms_widget_container .give-field-description {
				color: #aaaaaa;
				font-style: italic;
				margin: 0;
				padding-top: 8px;
				display: block;
			}

			.give_forms_widget_container select,
			.give_forms_widget_container textarea{
				margin: 5px 10px 0 0;
			}

			.give_forms_widget_container .give_forms_display_style_setting_row label[for$="tmp_display_style"]{
				display: flex;
				align-items: center;
			}

			.give_forms_widget_container .give_forms_display_style_setting_row label[for*="tmp_display_style-"]{
				display: flex;
			}

			.give_forms_widget_container .give_forms_display_style_setting_row label[for*="tmp_display_style-"] span:last-child{
				margin-left: 5px;
			}
		</style>
		<script>
			/**
			 * Display setting fields on basis of donation form setting.
			 *
			 * @since 2.7.0
			 * @param {Array} $el
			 */
			function showConditionalFieldWhenEditDonationFormSetting<?php echo esc_js( $this->widgetIdentifier ); ?>( $el ) {
				const $this        = $el,
					  $container   = $this.closest( '.give_forms_widget_container' ),
					  $loader      = jQuery( '.js-loader', $container ),
					  $oldSettings = jQuery( '.js-legacy-form-template-settings', $container ),
					  $newSettings = jQuery( '.js-new-form-template-settings', $container );

				$oldSettings.hide().removeClass( 'active' );
				$newSettings.hide().removeClass( 'active' );

				$loader.show();

				jQuery.post(
					ajaxurl,
					{
						action: 'give_get_form_template_id',
						formId: $this.val(),
						security: jQuery( 'input[name="_wpnonce"]', $container ).val(),
					},
					function( response ) {
						$loader.hide();

						// Exit if result is not successful.
						if (true === response.success) {
							if ('legacy' === response.data) {
								$oldSettings.show().addClass( 'active' );
							} else {
								$newSettings.show().addClass( 'active' );
							}
						}

						showConditionalFieldWhenEditDisplayStyleSetting<?php echo esc_js( $this->widgetIdentifier ); ?>( $this );
					},
				);
			}

			/**
			 * Display setting fields on basis of display_style setting.
			 *
			 * @since 2.7.0
			 * @param {Array} $el
			 */
			function showConditionalFieldWhenEditDisplayStyleSetting<?php echo esc_js( $this->widgetIdentifier ); ?>( $el ) {
				const $container              = $el.closest( '.give_forms_widget_container' ),
					  $fieldset               = jQuery( '.js-form-template-settings.active', $container ),
					  $parent                 = jQuery( 'p.give_forms_display_style_setting_row', $fieldset ),
					  isFormHasNewTemplate    = $fieldset.hasClass( 'js-new-form-template-settings' ),
					  isFormHasLegacyTemplate = $fieldset.hasClass( 'js-legacy-form-template-settings' );

				if (isFormHasLegacyTemplate) {
					const $continue_button_title = $parent.next();

					if ('onpage' === jQuery( 'input:checked', $parent ).val()) {
						$continue_button_title.hide();
					} else {
						$continue_button_title.show();
					}
				} else if (isFormHasNewTemplate) {
					if ('button' === jQuery( 'input:checked', $parent ).val()) {
						$fieldset.find( 'p' ).not( $parent ).show();
					} else {
						$fieldset.find( 'p' ).not( $parent ).hide();
					}
				}
			}

			/* Display style change handler. */
			jQuery( document ).on( 'change', '#<?php echo esc_js( $containerId ); ?> .give_forms_display_style_setting_row input', function() {
				showConditionalFieldWhenEditDisplayStyleSetting<?php echo esc_js( $this->widgetIdentifier ); ?>( jQuery( this ) );
			} );

			/* Donation form change handler. */
			jQuery( document ).on( 'change', '#<?php echo esc_js( $containerId ); ?> select.give-select', function() {
				const $container = jQuery(this).closest('.give_forms_widget_container'),
					  $parent = jQuery( this ).parent();

				$parent.removeClass('give-hidden');
				jQuery('.js-loader', $container ).addClass('give-hidden');

				// Render form template settings only if form selected.
				if( parseInt(jQuery( this ).val()) ) {
					showConditionalFieldWhenEditDonationFormSetting<?php echo esc_js( $this->widgetIdentifier ); ?>( jQuery( this ) );
				} else{
					jQuery( '#<?php echo esc_js( $containerId ); ?> .give-hidden' ).hide();
				}
			} );

			jQuery( '#<?php echo esc_js( $containerId ); ?> select.give-select' ).trigger('change');
		</script>
		<?php
	}
}

new Give_Forms_Widget();
