<?php
/**
 * Give Product Settings
 *
 * @author   WordImpress
 * @version  1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Products' ) ) :

	/**
	 * Give_Settings_Products.
	 */
	class Give_Settings_Products extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'products';
			$this->label = __( 'Products', 'give' );

			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'give_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'give_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'give_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''          	=> __( 'General', 'give' ),
				'display'       => __( 'Display', 'give' ),
				'inventory' 	=> __( 'Inventory', 'give' ),
				'downloadable' 	=> __( 'Downloadable Products', 'give' ),
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			Give_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			Give_Admin_Settings::save_fields( $settings, 'give-settings-2' );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( 'display' == $current_section ) {

				$settings = apply_filters( 'give_product_settings', array(

					array(
						'title' => __( 'Shop & Product Pages', 'give' ),
						'type' 	=> 'title',
						'desc' 	=> '',
						'id' 	=> 'catalog_options',
					),

					array(
						'title'    => __( 'Shop Page', 'give' ),
						'desc'     => '<br/>' . sprintf( __( 'The base page can also be used in your <a href="%s">product permalinks</a>.', 'give' ), admin_url( 'options-permalink.php' ) ),
						'id'       => 'give_shop_page_id',
						'type'     => 'single_select_page',
						'default'  => '',
						'class'    => 'wc-enhanced-select-nostd',
						'css'      => 'min-width:300px;',
						'desc_tip' => __( 'This sets the base page of your shop - this is where your product archive will be.', 'give' ),
					),

					array(
						'title'    => __( 'Shop Page Display', 'give' ),
						'desc'     => __( 'This controls what is shown on the product archive.', 'give' ),
						'id'       => 'give_shop_page_display',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'select',
						'options'  => array(
							''              => __( 'Show products', 'give' ),
							'subcategories' => __( 'Show categories', 'give' ),
							'both'          => __( 'Show categories &amp; products', 'give' ),
						),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Default Category Display', 'give' ),
						'desc'     => __( 'This controls what is shown on category archives.', 'give' ),
						'id'       => 'give_category_archive_display',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'select',
						'options'  => array(
							''              => __( 'Show products', 'give' ),
							'subcategories' => __( 'Show subcategories', 'give' ),
							'both'          => __( 'Show subcategories &amp; products', 'give' ),
						),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Default Product Sorting', 'give' ),
						'desc'     => __( 'This controls the default sort order of the catalog.', 'give' ),
						'id'       => 'give_default_catalog_orderby',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => 'menu_order',
						'type'     => 'select',
						'options'  => apply_filters( 'give_default_catalog_orderby_options', array(
							'menu_order' => __( 'Default sorting (custom ordering + name)', 'give' ),
							'popularity' => __( 'Popularity (sales)', 'give' ),
							'rating'     => __( 'Average Rating', 'give' ),
							'date'       => __( 'Sort by most recent', 'give' ),
							'price'      => __( 'Sort by price (asc)', 'give' ),
							'price-desc' => __( 'Sort by price (desc)', 'give' ),
						) ),
						'desc_tip' => true,
					),

					array(
						'title'         => __( 'Add to cart behaviour', 'give' ),
						'desc'          => __( 'Redirect to the cart page after successful addition', 'give' ),
						'id'            => 'give_cart_redirect_after_add',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),

					array(
						'desc'          => __( 'Enable AJAX add to cart buttons on archives', 'give' ),
						'id'            => 'give_enable_ajax_add_to_cart',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'end',
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'catalog_options',
					),

					array(
						'title' => __( 'Product Images', 'give' ),
						'type' 	=> 'title',
						'desc' 	=> sprintf( __( 'These settings affect the display and dimensions of images in your catalog - the display on the front-end will still be affected by CSS styles. After changing these settings you may need to <a target="_blank" href="%s">regenerate your thumbnails</a>.', 'give' ), 'https://wordpress.org/extend/plugins/regenerate-thumbnails/' ),
						'id' 	=> 'image_options',
					),

					array(
						'title'    => __( 'Catalog Images', 'give' ),
						'desc'     => __( 'This size is usually used in product listings. (W x H)', 'give' ),
						'id'       => 'shop_catalog_image_size',
						'css'      => '',
						'type'     => 'image_width',
						'default'  => array(
							'width'  => '300',
							'height' => '300',
							'crop'   => 1,
						),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Single Product Image', 'give' ),
						'desc'     => __( 'This is the size used by the main image on the product page. (W x H)', 'give' ),
						'id'       => 'shop_single_image_size',
						'css'      => '',
						'type'     => 'image_width',
						'default'  => array(
							'width'  => '600',
							'height' => '600',
							'crop'   => 1,
						),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Product Thumbnails', 'give' ),
						'desc'     => __( 'This size is usually used for the gallery of images on the product page. (W x H)', 'give' ),
						'id'       => 'shop_thumbnail_image_size',
						'css'      => '',
						'type'     => 'image_width',
						'default'  => array(
							'width'  => '180',
							'height' => '180',
							'crop'   => 1,
						),
						'desc_tip' => true,
					),

					array(
						'title'         => __( 'Product Image Gallery', 'give' ),
						'desc'          => __( 'Enable Lightbox for product images', 'give' ),
						'id'            => 'give_enable_lightbox',
						'default'       => 'yes',
						'desc_tip'      => __( 'Include Give\'s lightbox. Product gallery images will open in a lightbox.', 'give' ),
						'type'          => 'checkbox',
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'image_options',
					),

				));
			} elseif ( 'inventory' == $current_section ) {

				$settings = apply_filters( 'give_inventory_settings', array(

					array(
						'title' => __( 'Inventory', 'give' ),
						'type' 	=> 'title',
						'desc' 	=> '',
						'id' 	=> 'product_inventory_options',
					),

					array(
						'title'   => __( 'Manage Stock', 'give' ),
						'desc'    => __( 'Enable stock management', 'give' ),
						'id'      => 'give_manage_stock',
						'default' => 'yes',
						'type'    => 'checkbox',
					),

					array(
						'title'             => __( 'Hold Stock (minutes)', 'give' ),
						'desc'              => __( 'Hold stock (for unpaid orders) for x minutes. When this limit is reached, the pending order will be cancelled. Leave blank to disable.', 'give' ),
						'id'                => 'give_hold_stock_minutes',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'css'               => 'width: 80px;',
						'default'           => '60',
						'autoload'          => false,
					),

					array(
						'title'         => __( 'Notifications', 'give' ),
						'desc'          => __( 'Enable low stock notifications', 'give' ),
						'id'            => 'give_notify_low_stock',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
						'autoload'      => false,
					),

					array(
						'desc'          => __( 'Enable out of stock notifications', 'give' ),
						'id'            => 'give_notify_no_stock',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'end',
						'autoload'      => false,
					),

					array(
						'title'    => __( 'Notification Recipient(s)', 'give' ),
						'desc'     => __( 'Enter recipients (comma separated) that will receive this notification.', 'give' ),
						'id'       => 'give_stock_email_recipient',
						'type'     => 'text',
						'default'  => get_option( 'admin_email' ),
						'css'      => 'width: 250px;',
						'autoload' => false,
						'desc_tip' => true,
					),

					array(
						'title'             => __( 'Low Stock Threshold', 'give' ),
						'desc'              => __( 'When product stock reaches this amount you will be notified via email.', 'give' ),
						'id'                => 'give_notify_low_stock_amount',
						'css'               => 'width:50px;',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'default'           => '2',
						'autoload'          => false,
						'desc_tip'          => true,
					),

					array(
						'title'             => __( 'Out Of Stock Threshold', 'give' ),
						'desc'              => __( 'When product stock reaches this amount the stock status will change to "out of stock" and you will be notified via email. This setting does not affect existing "in stock" products.', 'give' ),
						'id'                => 'give_notify_no_stock_amount',
						'css'               => 'width:50px;',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'default'           => '0',
						'desc_tip'          => true,
					),

					array(
						'title'    => __( 'Out Of Stock Visibility', 'give' ),
						'desc'     => __( 'Hide out of stock items from the catalog', 'give' ),
						'id'       => 'give_hide_out_of_stock_items',
						'default'  => 'no',
						'type'     => 'checkbox',
					),

					array(
						'title'    => __( 'Stock Display Format', 'give' ),
						'desc'     => __( 'This controls how stock is displayed on the frontend.', 'give' ),
						'id'       => 'give_stock_format',
						'css'      => 'min-width:150px;',
						'class'    => 'wc-enhanced-select',
						'default'  => '',
						'type'     => 'select',
						'options'  => array(
							''           => __( 'Always show stock e.g. "12 in stock"', 'give' ),
							'low_amount' => __( 'Only show stock when low e.g. "Only 2 left in stock" vs. "In Stock"', 'give' ),
							'no_amount'  => __( 'Never show stock amount', 'give' ),
						),
						'desc_tip' => true,
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'product_inventory_options',
					),

				));

			} elseif ( 'downloadable' == $current_section ) {
				$settings = apply_filters( 'give_downloadable_products_settings', array(
					array(
						'title' => __( 'Downloadable Products', 'give' ),
						'type' 	=> 'title',
						'id' 	=> 'digital_download_options',
					),

					array(
						'title'    => __( 'File Download Method', 'give' ),
						'desc'     => __( 'Forcing downloads will keep URLs hidden, but some servers may serve large files unreliably. If supported, <code>X-Accel-Redirect</code>/ <code>X-Sendfile</code> can be used to serve downloads instead (server requires <code>mod_xsendfile</code>).', 'give' ),
						'id'       => 'give_file_download_method',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => 'force',
						'desc_tip' => true,
						'options'  => array(
							'force'     => __( 'Force Downloads', 'give' ),
							'xsendfile' => __( 'X-Accel-Redirect/X-Sendfile', 'give' ),
							'redirect'  => __( 'Redirect only', 'give' ),
						),
						'autoload' => false,
					),

					array(
						'title'         => __( 'Access Restriction', 'give' ),
						'desc'          => __( 'Downloads require login', 'give' ),
						'id'            => 'give_downloads_require_login',
						'type'          => 'checkbox',
						'default'       => 'no',
						'desc_tip'      => __( 'This setting does not apply to guest purchases.', 'give' ),
						'checkboxgroup' => 'start',
						'autoload'      => false,
					),

					array(
						'desc'          => __( 'Grant access to downloadable products after payment', 'give' ),
						'id'            => 'give_downloads_grant_access_after_payment',
						'type'          => 'checkbox',
						'default'       => 'yes',
						'desc_tip'      => __( 'Enable this option to grant access to downloads when orders are "processing", rather than "completed".', 'give' ),
						'checkboxgroup' => 'end',
						'autoload'      => false,
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'digital_download_options',
					),

				));

			} else {
				$settings = apply_filters( 'give_products_general_settings', array(
					array(
						'title' 	=> __( 'Measurements', 'give' ),
						'type' 		=> 'title',
						'id' 		=> 'product_measurement_options',
					),

					array(
						'title'    => __( 'Weight Unit', 'give' ),
						'desc'     => __( 'This controls what unit you will define weights in.', 'give' ),
						'id'       => 'give_weight_unit',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => 'kg',
						'type'     => 'select',
						'options'  => array(
							'kg'  => __( 'kg', 'give' ),
							'g'   => __( 'g', 'give' ),
							'lbs' => __( 'lbs', 'give' ),
							'oz'  => __( 'oz', 'give' ),
						),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Dimensions Unit', 'give' ),
						'desc'     => __( 'This controls what unit you will define lengths in.', 'give' ),
						'id'       => 'give_dimension_unit',
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => 'cm',
						'type'     => 'select',
						'options'  => array(
							'm'  => __( 'm', 'give' ),
							'cm' => __( 'cm', 'give' ),
							'mm' => __( 'mm', 'give' ),
							'in' => __( 'in', 'give' ),
							'yd' => __( 'yd', 'give' ),
						),
						'desc_tip' => true,
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'product_measurement_options',
					),

					array(
						'title' => __( 'Reviews', 'give' ),
						'type' 	=> 'title',
						'desc' 	=> '',
						'id' 	=> 'product_rating_options',
					),

					array(
						'title'           => __( 'Product Ratings', 'give' ),
						'desc'            => __( 'Enable ratings on reviews', 'give' ),
						'id'              => 'give_enable_review_rating',
						'default'         => 'yes',
						'type'            => 'checkbox',
						'checkboxgroup'   => 'start',
						'show_if_checked' => 'option',
					),

					array(
						'desc'            => __( 'Ratings are required to leave a review', 'give' ),
						'id'              => 'give_review_rating_required',
						'default'         => 'yes',
						'type'            => 'checkbox',
						'checkboxgroup'   => '',
						'show_if_checked' => 'yes',
						'autoload'        => false,
					),

					array(
						'desc'            => __( 'Show "verified owner" label for customer reviews', 'give' ),
						'id'              => 'give_review_rating_verification_label',
						'default'         => 'yes',
						'type'            => 'checkbox',
						'checkboxgroup'   => '',
						'show_if_checked' => 'yes',
						'autoload'        => false,
					),

					array(
						'desc'            => __( 'Only allow reviews from "verified owners"', 'give' ),
						'id'              => 'give_review_rating_verification_required',
						'default'         => 'no',
						'type'            => 'checkbox',
						'checkboxgroup'   => 'end',
						'show_if_checked' => 'yes',
						'autoload'        => false,
					),

					array(
						'type' 	=> 'sectionend',
						'id' 	=> 'product_rating_options',
					),

				));
			}

			return apply_filters( 'give_get_settings_' . $this->id, $settings, $current_section );
		}
	}

endif;

return new Give_Settings_Products();
