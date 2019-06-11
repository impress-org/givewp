<?php
/**
 * The class contains logic to clone a donation form.
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2.0
 */

if ( ! class_exists( 'Give_Form_Duplicator' ) ) {

	/**
	 * Give_Form_Duplicator class
	 */
	class Give_Form_Duplicator {

		/**
		 * Constructor Function
		 */
		public function __construct() {

			// Add the 'Clone Form' to Row Actions.
			add_filter( 'post_row_actions', array( $this, 'row_action' ), 10, 2 );

			// Run admin_action hook.
			add_action( 'admin_action_give_duplicate_form', array( $this, 'handler' ) );
		}


		/**
		 * Adds the 'Clone Form' in the row actions.
		 *
		 * @param array          $actions Array of Row Actions.
		 * @param WP_Post Object $post    Post Object.
		 *
		 * @since 2.2.0
		 *
		 * @return array
		 */
		public function row_action( $actions, $post ) {

			// @codingStandardsIgnoreStart

			if ( isset( $_GET['post_type'] ) && 'give_forms' === give_clean( $_GET['post_type'] ) ) { // WPCS: input var ok.
				if ( current_user_can( 'edit_posts' ) ) {
					$actions['duplicate_form'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						wp_nonce_url( add_query_arg(
							array(
								'action'  => 'give_duplicate_form',
								'form_id' => $post->ID,
							),
							admin_url( 'admin.php' )
						), 'give-duplicate-form' ),
						__( 'Duplicate', 'give' )
					);
				}
			}

			// @codingStandardsIgnoreEnd

			return $actions;
		}


		/**
		 * Clones the Form
		 *
		 * @since 2.2.0
		 *
		 * @return void
		 */
		public function handler() {
			// Validate action.
			// @codingStandardsIgnoreStart
			if (
				! isset( $_REQUEST['form_id'] )
				|| ! isset( $_REQUEST['action'] )
				|| ( 'give_duplicate_form' !== $_REQUEST['action'] )
			) {
				wp_die( esc_html__( 'Form ID not found in the query string', 'give' ) );

			} elseif ( ! wp_verify_nonce( give_clean( $_REQUEST['_wpnonce'] ), 'give-duplicate-form' ) ) {

				wp_die( esc_html__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ) );
			}
			// @codingStandardsIgnoreEnd

			$form_id      = give_clean( $_REQUEST['form_id'] ); // @codingStandardsIgnoreLine
			$post_data    = get_post( $form_id );
			$current_user = wp_get_current_user();
			$error_notice = sprintf(
				/* translators: %s: Form ID */
				esc_html__( 'Cloning failed. Form with ID %s does not exist.', 'give' ),
				absint( $form_id )
			);

			if ( isset( $post_data ) && null !== $post_data ) {

				$args = array(
					'comment_status' => $post_data->comment_status,
					'ping_status'    => $post_data->ping_status,
					'post_author'    => $current_user->ID,
					'post_content'   => $post_data->post_content,
					'post_excerpt'   => $post_data->post_excerpt,
					'post_name'      => $post_data->post_name,
					'post_parent'    => $post_data->post_parent,
					'post_password'  => $post_data->post_password,
					'post_status'    => 'draft',
					'post_title'     => $post_data->post_title,
					'post_type'      => $post_data->post_type,
					'to_ping'        => $post_data->to_ping,
					'menu_order'     => $post_data->menu_order,
				);

				// Get the ID of the cloned post.
				$duplicate_form_id = wp_insert_post( $args );

				$this->duplicate_taxonomies( $duplicate_form_id, $post_data );
				$this->duplicate_meta_data( $duplicate_form_id, $post_data );
				$this->reset_stats( $duplicate_form_id );

				/**
				 * Fire the action
				 *
				 * @since 2.2.0
				 *
				 * @param int $duplicate_form_id Duplicated form ID.
				 * @param int $form_id           Form ID.
				 */
				do_action( 'give_form_duplicated', $duplicate_form_id, $form_id );

				if ( ! is_wp_error( $duplicate_form_id ) ) {
					// Redirect to the cloned form editor page.
					wp_safe_redirect(
						add_query_arg(
							array(
								'action' => 'edit',
								'post'   => $duplicate_form_id,
							),
							admin_url( 'post.php' )
						)
					);
				} else {
					wp_die( $error_notice ); // @codingStandardsIgnoreLine
				}

				exit;

			} else {

				wp_die( $error_notice ); // @codingStandardsIgnoreLine
			}
		}


		/**
		 * Clone taxonomies
		 *
		 * @since  2.2.0
		 * @access private
		 *
		 * @param int     $new_form_id New form ID.
		 * @param WP_Post $old_form    Old form object.
		 */
		private function duplicate_taxonomies( $new_form_id, $old_form ) {
			// Get the taxonomies of the post type `give_forms`.
			$taxonomies = get_object_taxonomies( $old_form->post_type );

			foreach ( $taxonomies as $taxonomy ) {

				$post_terms = wp_get_object_terms(
					$old_form->ID,
					$taxonomy,
					array(
						'fields' => 'slugs',
					)
				);

				wp_set_object_terms(
					$new_form_id,
					$post_terms,
					$taxonomy,
					false
				);
			}
		}


		/**
		 * Clone meta data
		 *
		 * @since  2.2.0
		 * @access private
		 *
		 * @param int     $new_form_id New Form ID.
		 * @param WP_Post $old_form    Old form object.
		 */
		private function duplicate_meta_data( $new_form_id, $old_form ) {
			global $wpdb;

			// Clone the metadata of the form.
			$post_meta_query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->formmeta} WHERE form_id=%s", $old_form->ID );

			$post_meta_data = $wpdb->get_results( $post_meta_query ); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.

			if ( ! empty( $post_meta_data ) ) {

				$duplicate_query        = "INSERT INTO {$wpdb->formmeta} (form_id, meta_key, meta_value) ";
				$duplicate_query_select = array();

				foreach ( $post_meta_data as $meta_data ) {
					$meta_key                 = $meta_data->meta_key;
					$meta_value               = $meta_data->meta_value;
					$duplicate_query_select[] = $wpdb->prepare( 'SELECT %s, %s, %s', $new_form_id, $meta_key, $meta_value );
				}

				$duplicate_query .= implode( ' UNION ALL ', $duplicate_query_select );

				$wpdb->query( $duplicate_query ); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.
			}
		}

		/**
		 * Reset stats for cloned form
		 *
		 * @since  2.2.0
		 * @access private
		 *
		 * @param int $new_form_id New Form ID.
		 */
		private function reset_stats( $new_form_id ) {
			global $wpdb;

			$meta_keys = array( '_give_form_sales', '_give_form_earnings' );

			/**
			 * Fire the filter
			 *
			 * @since  2.2.0
			 */
			$meta_keys = apply_filters( 'give_duplicate_form_reset_stat_meta_keys', $meta_keys );
			$meta_keys = 'meta_key=\'' . implode( '\' OR meta_key=\'', $meta_keys ) . '\'';

			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE $wpdb->formmeta
					SET meta_value=0
					WHERE form_id=%d
					AND ({$meta_keys})
					",
					$new_form_id
				)
			);
		}
	}

	new Give_Form_Duplicator();
}
