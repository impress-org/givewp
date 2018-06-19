<?php
/**
 * The class contains logic to clone a donation form.
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2.0
 */

if ( ! class_exists( 'Give_Clone_Forms' ) ) {

	/**
	 * Give_Clone_Forms class
	 */
	class Give_Clone_Forms {

		/**
		 * Constructor Function
		 */
		public function __construct() {

			// Add the 'Clone Form' to Row Actions.
			add_filter( 'post_row_actions', array( $this, 'add_clone_form_row_action' ), 10, 2 );

			// Run admin_action hook.
			add_action( 'admin_action_give_clone_form', array( $this, 'clone_the_form' ) );
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
		public function add_clone_form_row_action( $actions, $post ) {

			// @codingStandardsIgnoreStart
			
			if ( isset( $_GET['post_type'] ) && 'give_forms' === give_clean( $_GET['post_type'] ) ) { // WPCS: input var ok.
				if ( current_user_can( 'edit_posts' ) ) {
					$actions['clone_form'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						wp_nonce_url( add_query_arg(
							array(
								'action'  => 'give_clone_form',
								'form_id' => $post->ID,
							),
							admin_url( 'admin.php' )
						), 'give-clone-form' ),
						__( 'Clone Form', 'give' )
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
		public function clone_the_form() {

			// Global $wpdb object.
			global $wpdb;

			// @codingStandardsIgnoreStart

			if ( ! isset( $_REQUEST['form_id'] ) ) {
				wp_die( esc_html__( 'Form ID not found in the query string', 'give' ) );
			}

			$nonce        = give_clean( $_REQUEST['_wpnonce'] );
			$form_id      = give_clean( $_REQUEST['form_id'] );
			$post         = get_post( $form_id );
			$current_user = wp_get_current_user();
			$post_author  = $current_user->ID;

			// @codingStandardsIgnoreEnd

			// Verify nonce.
			if ( ! wp_verify_nonce( $nonce, 'give-clone-form' ) ) {
				wp_die( esc_html__( 'Nonce verification failed', 'give' ) );
			}

			if ( isset( $post ) && null !== $post ) {

				$args = array(
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_name'      => $post->post_name,
					'post_parent'    => $post->post_parent,
					'post_password'  => $post->post_password,
					'post_status'    => 'draft',
					'post_title'     => $post->post_title,
					'post_type'      => $post->post_type,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order,
				);

				// Get the ID of the cloned post.
				$clone_form_id = wp_insert_post( $args );

				// Get the taxonomies of the post type `give_forms`.
				$taxonomies = get_object_taxonomies( $post->post_type );

				foreach ( $taxonomies as $taxonomy ) {

					$post_terms = wp_get_object_terms(
						$form_id,
						$taxonomy,
						array(
							'fields' => 'slugs',
						)
					);

					wp_set_object_terms( $clone_form_id, $post_terms, $taxonomy, false );
				}

				// Clone the metadata of the form.
				$post_meta_query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->formmeta} WHERE form_id=%s", $form_id );

				$post_meta_data = $wpdb->get_results( $post_meta_query ); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.

				if ( ! empty( $post_meta_data ) ) {

					$clone_query = "INSERT INTO {$wpdb->formmeta} (form_id, meta_key, meta_value) ";

					foreach ( $post_meta_data as $meta_data ) {
						$meta_key             = $meta_data->meta_key;
						$meta_value           = $meta_data->meta_value;
						$clone_query_select[] = $wpdb->prepare( 'SELECT %s, %s, %s', $clone_form_id, $meta_key, $meta_value );
					}

					$clone_query .= implode( ' UNION ALL ', $clone_query_select );

					$wpdb->query( $clone_query ); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.
				}

				// Redirect to the cloned form editor page.
				wp_safe_redirect(
					add_query_arg(
						array(
							'action' => 'edit',
							'post'   => $clone_form_id,
						),
						admin_url( 'post.php' )
					)
				);

				exit;

			} else {

				/* translators: %s: Form ID */
				wp_die( sprintf( esc_html__( 'Cloning failed. Form with ID %s does not exist.', 'give' ), esc_html( $form_id ) ) );
			}
		}
	}

	new Give_Clone_Forms();
}
