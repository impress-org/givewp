<?php
/**
 * Batch Forms Export Class
 *
 * This class handles form export.
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2017, WordImpress
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
	 * Our export type. Used for export-type specific filters/actions./
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
			'ID'                      => __( 'ID', 'give' ),
			'post_name'               => __( 'Slug', 'give' ),
			'post_title'              => __( 'Name', 'give' ),
			'post_date'               => __( 'Date Created', 'give' ),
			'post_author'             => __( 'Author', 'give' ),
			'post_content'            => __( 'Description', 'give' ),
			'post_excerpt'            => __( 'Excerpt', 'give' ),
			'post_status'             => __( 'Status', 'give' ),
			'categories'              => __( 'Categories', 'give' ),
			'tags'                    => __( 'Tags', 'give' ),
			'give_price'              => __( 'Price', 'give' ),
			'_thumbnail_id'           => __( 'Featured Image', 'give' ),
			'_give_form_sales'        => __( 'Donations', 'give' ),
			'_give_download_earnings' => __( 'Income', 'give' ),
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
			'_give_form_sales',
			'_give_form_earnings'
		);

		$args = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		$forms = new WP_Query( $args );

		if ( $forms->posts ) {
			foreach ( $forms->posts as $form ) {

				$row = [];

				foreach ( $this->csv_cols() as $key => $value ) {

					// Setup default value/
					$row[ $key ] = '';

					if ( in_array( $key, $meta ) ) {

						switch ( $key ) {

							case '_thumbnail_id' :

								$image_id    = get_post_thumbnail_id( $form->ID );
								$row[ $key ] = wp_get_attachment_url( $image_id );

								break;

							case 'give_price' :

								if ( give_has_variable_prices( $form->ID ) ) {

									$prices = [];
									foreach ( give_get_variable_prices( $form->ID ) as $price ) {
										$prices[] = $price['name'] . ': ' . $price['amount'];
									}

									$row[ $key ] = implode( ' | ', $prices );

								} else {

									$row[ $key ] = give_get_form_price( $form->ID );

								}

								break;

							default :

								$row[ $key ] = give_get_meta( $form->ID, $key, TRUE );

								break;

						}

					} elseif ( isset( $form->$key ) ) {

						switch ( $key ) {

							case 'post_author' :

								$row[ $key ] = get_the_author_meta( 'user_login', $form->post_author );

								break;

							default :

								$row[ $key ] = $form->$key;

								break;
						}

					} elseif ( 'tags' == $key ) {

						$terms = get_the_terms( $form->ID, 'form_tag' );
						if ( $terms ) {
							$terms       = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}


					} elseif ( 'categories' == $key ) {

						$terms = get_the_terms( $form->ID, 'form_category' );
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
	 * Return the calculated completion percentage.
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

		$forms  = new WP_Query( $args );
		$total      = (int) $forms->post_count;
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