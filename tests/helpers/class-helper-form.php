<?php

/**
 * Class Give_Helper_Form.
 *
 * Helper class to create and delete a donation form.
 */
class Give_Helper_Form extends WP_UnitTestCase {

	/**
	 * Delete a download.
	 *
	 * @since 1.0
	 *
	 * @param int $download_id ID of the download to delete.
	 */
	public static function delete_download( $download_id ) {

		// Delete the post
		wp_delete_post( $download_id, true );

	}

	/**
	 * Create a simple download.
	 *
	 * @since 1.0
	 */
	public static function create_simple_form() {

		$post_id = wp_insert_post( array(
			'post_title'  => 'Test Donation Form',
			'post_name'   => 'test-donation-form',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$meta = array(
			'give_price'                      => '20.00',
			'_variable_pricing'               => 0,
			'give_variable_prices'            => false,
			'give_download_files'             => array_values( $_download_files ),
			'_give_download_limit'            => 20,
			'_give_hide_purchase_link'        => 1,
			'give_product_notes'              => 'Purchase Notes',
			'_give_product_type'              => 'default',
			'_give_download_earnings'         => 40,
			'_give_download_sales'            => 2,
			'_give_download_limit_override_1' => 1,
			'give_sku'                        => 'sku_0012'
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	/**
	 * Create a variable priced download.
	 *
	 * @since 1.0
	 */
	public static function create_variable_download() {

		$post_id = wp_insert_post( array(
			'post_title'  => 'Variable Test Download Product',
			'post_name'   => 'variable-test-download-product',
			'post_type'   => 'download',
			'post_status' => 'publish'
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Simple',
				'amount' => 20
			),
			array(
				'name'   => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name'      => 'File 1',
				'file'      => 'http://localhost/file1.jpg',
				'condition' => 0,
			),
			array(
				'name'      => 'File 2',
				'file'      => 'http://localhost/file2.jpg',
				'condition' => 'all',
			),
		);

		$meta = array(
			'give_price'                      => '0.00',
			'_variable_pricing'               => 1,
			'_give_price_options_mode'        => 'on',
			'give_variable_prices'            => array_values( $_variable_pricing ),
			'give_download_files'             => array_values( $_download_files ),
			'_give_download_limit'            => 20,
			'_give_hide_purchase_link'        => 1,
			'give_product_notes'              => 'Purchase Notes',
			'_give_product_type'              => 'default',
			'_give_download_earnings'         => 120,
			'_give_download_sales'            => 6,
			'_give_download_limit_override_1' => 1,
			'give_sku'                        => 'sku_0012',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	/**
	 * Create a bundled download.
	 *
	 * @since 1.0
	 */
	public static function create_bundled_download() {

		$post_id = wp_insert_post( array(
			'post_title'  => 'Bundled Test Download Product',
			'post_name'   => 'bundled-test-download-product',
			'post_type'   => 'download',
			'post_status' => 'publish'
		) );

		$simple_download   = Give_Helper_Form::create_simple_form();
		$variable_download = Give_Helper_Form::create_variable_download();

		$meta = array(
			'give_price'              => '9.99',
			'_variable_pricing'       => 1,
			'give_variable_prices'    => false,
			'give_download_files'     => array(),
			'_give_bundled_products'  => array( $simple_download->ID, $variable_download->ID ),
			'_give_download_limit'    => 20,
			'give_product_notes'      => 'Bundled Purchase Notes',
			'_give_product_type'      => 'bundle',
			'_give_download_earnings' => 120,
			'_give_download_sales'    => 12,
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

}
