<?php
/**
 * Batch Forms Export Class
 *
 * This class handles download products export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Batch_Forms_Export Class
 *
 * @since 1.5
 */
class Give_Batch_Forms_Export extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.5
	 */
	public $export_type = 'forms';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'ID'                      => esc_html__( 'ID', 'give' ),
			'post_name'               => esc_html__( 'Slug', 'give' ),
			'post_title'              => esc_html__( 'Name', 'give' ),
			'post_date'               => esc_html__( 'Date Created', 'give' ),
			'post_author'             => esc_html__( 'Author', 'give' ),
			'post_content'            => esc_html__( 'Description', 'give' ),
			'post_excerpt'            => esc_html__( 'Excerpt', 'give' ),
			'post_status'             => esc_html__( 'Status', 'give' ),
			'categories'              => esc_html__( 'Categories', 'give' ),
			'tags'                    => esc_html__( 'Tags', 'give' ),
			'give_price'              => esc_html__( 'Price', 'give' ),
			'_thumbnail_id'           => esc_html__( 'Featured Image', 'give' ),
			'_give_form_sales'        => esc_html__( 'Donations', 'give' ),
			'_give_download_earnings' => esc_html__( 'Income', 'give' ),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.5
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		$meta = array(
			'give_price',
			'_thumbnail_id',
			'give_sku',
			'give_product_notes',
			'_give_form_sales',
			'_give_download_earnings'
		);

		$args = array(
			'post_type'      => 'download',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		$downloads = new WP_Query( $args );

		if ( $downloads->posts ) {
			foreach ( $downloads->posts as $download ) {

				$row = array();

				foreach ( $this->csv_cols() as $key => $value ) {

					// Setup default value
					$row[ $key ] = '';

					if ( in_array( $key, $meta ) ) {

						switch ( $key ) {

							case '_thumbnail_id' :

								$image_id    = get_post_thumbnail_id( $download->ID );
								$row[ $key ] = wp_get_attachment_url( $image_id );

								break;

							case 'give_price' :

								if ( give_has_variable_prices( $download->ID ) ) {

									$prices = array();
									foreach ( give_get_variable_prices( $download->ID ) as $price ) {
										$prices[] = $price['name'] . ': ' . $price['amount'];
									}

									$row[ $key ] = implode( ' | ', $prices );

								} else {

									$row[ $key ] = give_get_download_price( $download->ID );

								}

								break;

							case '_give_files' :


								$files = array();
								foreach ( give_get_download_files( $download->ID ) as $file ) {
									$files[] = $file['file'];
								}

								$row[ $key ] = implode( ' | ', $files );

								break;

							default :

								$row[ $key ] = get_post_meta( $download->ID, $key, true );

								break;

						}

					} elseif ( isset( $download->$key ) ) {

						switch ( $key ) {

							case 'post_author' :

								$row[ $key ] = get_the_author_meta( 'user_login', $download->post_author );

								break;

							default :

								$row[ $key ] = $download->$key;

								break;
						}

					} elseif ( 'tags' == $key ) {

						$terms = get_the_terms( $download->ID, 'download_tag' );
						if ( $terms ) {
							$terms       = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}


					} elseif ( 'categories' == $key ) {

						$terms = get_the_terms( $download->ID, 'download_category' );
						if ( $terms ) {
							$terms       = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}

					}

				}

				$data[] = $row;

			}

			$data = apply_filters( 'give_export_get_data', $data );
			$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

			return $data;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$args = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => - 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		);

		$downloads  = new WP_Query( $args );
		$total      = (int) $downloads->post_count;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}