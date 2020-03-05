<?php

/**
 * Class for managing comments
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.2.0
 */
class Give_Comment {
	/**
	 * Instance.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Comment Types.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var array
	 */
	private $comment_types;

	/**
	 * Comment Table.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var Give_DB_Comments
	 */
	public $db;

	/**
	 * Comment Meta Table.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var Give_DB_Comment_Meta
	 */
	public $db_meta;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.2.0
	 * @access pu
	 * @return Give_Comment
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function init() {
		$this->db      = new Give_DB_Comments();
		$this->db_meta = new Give_DB_Comment_Meta();

		/**
		 * Filter the comment type
		 *
		 * @since 2.2.0
		 */
		$this->comment_types = apply_filters(
			'give_comment_type',
			self::get_comment_types( array( 'payment', 'donor' ) )
		);

		// Backward compatibility.
		if ( ! give_has_upgrade_completed( 'v230_move_donation_note' ) ) {
			add_action( 'pre_get_comments', array( $this, 'hide_comments' ), 10 );
			add_filter( 'comments_clauses', array( $this, 'hide_comments_pre_wp_41' ), 10, 1 );
			add_filter( 'comment_feed_where', array( $this, 'hide_comments_from_feeds' ), 10, 1 );
			add_filter( 'wp_count_comments', array( $this, 'remove_comments_from_comment_counts' ), 10, 2 );
			add_filter( 'get_comment_author', array( $this, '__get_comment_author' ), 10, 3 );
		}
	}

	/**
	 * Insert/Update comment
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $comment_args Comment arguments.
	 *
	 * @return int|WP_Error
	 */
	public static function add( $comment_args = array() ) {
		// Backward compatibility.
		$func_args  = func_get_args();
		$comment_id = self::_bc_add( $func_args );
		if ( ! is_null( $comment_id ) ) {
			return $comment_id;
		}

		// Backward compatibility.
		if ( is_numeric( $comment_args ) ) {
			$comment_args = array(
				'comment_parent'  => $func_args[0],
				'comment_content' => $func_args[1],
				'comment_type'    => 'payment' === $func_args[2] ? 'donation' : $func_args[1],
			);
		}

		$comment_args = wp_parse_args(
			$comment_args,
			array(
				'comment_ID'     => 0,
				'comment_parent' => 0,
				'comment_type'   => 'general',
			)
		);

		$is_existing_comment = array_key_exists( 'comment_ID', $comment_args ) && ! empty( $comment_args['comment_ID'] );
		$action_type         = $is_existing_comment ? 'update' : 'insert';

		/**
		 * Fires before inserting/updating payment|donor comment.
		 *
		 * @param int    $id   Payment|Donor ID.
		 * @param string $note Comment text.
		 *
		 * @since 1.0
		 */
		do_action(
			"give_pre_{$action_type}_{$comment_args['comment_type']}_note",
			$comment_args['comment_parent'],
			$comment_args['comment_content'],
			$comment_args
		);

		$comment_id = Give()->comment->db->add(
			array(
				'comment_ID'      => absint( $comment_args['comment_ID'] ),
				'comment_content' => $comment_args['comment_content'],
				'comment_parent'  => $comment_args['comment_parent'],
				'comment_type'    => $comment_args['comment_type'],
			)
		);

		/**
		 * Fires after payment|donor comment inserted/updated.
		 *
		 * @param int    $comment_id Comment ID.
		 * @param int    $id         Payment|Donor ID.
		 * @param string $note       Comment text.
		 *
		 * @since 1.0
		 */
		do_action(
			"give_{$action_type}_{$comment_args['comment_type']}_note",
			$comment_args['comment_ID'],
			$comment_args['comment_parent'],
			$comment_args['comment_content'],
			$comment_args
		);

		return $comment_id;
	}


	/**
	 * Delete comment
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param int $comment_id The comment ID to delete.
	 *
	 * @since  1.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function delete( $comment_id ) {
		// Backward compatibility.
		$func_args = func_get_args();
		$ret       = self::_bc_delete( $func_args );
		if ( ! is_null( $ret ) ) {
			return $ret;
		}

		$ret = false;

		// Bailout
		if ( empty( $comment_id ) ) {
			return $ret;
		}

		/* @var stdClass $comment */
		$comment = Give()->comment->db->get_by( 'comment_ID', $comment_id );

		if ( ! is_object( $comment ) ) {
			return $ret;
		}

		$comment_type   = $comment->comment_type;
		$comment_parent = $comment->comment_parent;

		/**
		 * Fires before deleting donation note.
		 *
		 * @param int $comment_id Comment ID.
		 * @param int $id         Payment|Donor ID.
		 *
		 * @since 1.0
		 */
		do_action( "give_pre_delete_{$comment_type}_note", $comment_id, $comment_parent );

		$ret = Give()->comment->db->delete( $comment_id );

		// Delete comment meta.
		Give()->comment->db_meta->delete_all_meta( $comment_id );

		/**
		 * Fires after donation note deleted.
		 *
		 * @param int  $comment_id Note ID.
		 * @param int  $id         Payment|Donor ID.
		 * @param bool $ret        Flag to check if comment deleted or not.
		 *
		 * @since 1.0
		 */
		do_action( "give_post_delete_{$comment_type}_note", $comment_id, $comment_parent, $ret );

		return $ret;
	}


	/**
	 * Get comments
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $comment_args
	 *
	 * @return array
	 */
	public static function get( $comment_args ) {
		global $wpdb;

		// Backward compatibility.
		$func_args = func_get_args();
		$comments  = self::_bc_get( $func_args );
		if ( ! is_null( $comments ) ) {
			return $comments;
		}

		// Backward compatibility.
		if ( is_numeric( $comment_args ) ) {
			$comment_args = array(
				'comment_parent' => $func_args[0],
				'comment_type'   => 'payment' === $func_args[1] ? 'donation' : $func_args[1],
			);
		}

		$comments = $wpdb->get_results( Give()->comment->db->get_sql( $comment_args ) );

		return $comments;
	}

	/**
	 * Exclude comments from showing in Recent
	 * Comments widgets
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param object $query WordPress Comment Query Object.
	 *
	 * @return void
	 */
	public function hide_comments( $query ) {
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.1', '>=' ) ) {
			$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
			if ( ! is_array( $types ) ) {
				$types = array( $types );
			}

			$types = array_filter( array_merge( $types, $this->comment_types ) );

			$query->query_vars['type__not_in'] = $types;
		}
	}

	/**
	 * Exclude notes (comments) from showing in Recent Comments widgets
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $clauses Comment clauses for comment query.
	 *
	 * @return array $clauses Updated comment clauses.
	 */
	public function hide_comments_pre_wp_41( $clauses ) {
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.1', '<' ) ) {
			foreach ( $this->comment_types as $comment_type ) {
				$clauses['where'] .= " AND comment_type != \"{$comment_type}\"";
			}
		}

		return $clauses;
	}

	/**
	 * Exclude notes (comments) from showing in comment feeds
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param string $where
	 *
	 * @return string $where
	 */
	public function hide_comments_from_feeds( $where ) {
		global $wpdb;

		foreach ( $this->comment_types as $comment_type ) {
			$where .= $wpdb->prepare( ' AND comment_type!=%s', $comment_type );
		}

		return $where;
	}

	/**
	 * Remove Give Comments from the wp_count_comments function
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $stats   (empty from core filter).
	 * @param int   $post_id Post ID.
	 *
	 * @return array|object Array of comment counts.
	 */
	public function remove_comments_from_comment_counts( $stats, $post_id ) {
		global $wpdb;

		$post_id = (int) $post_id;

		if ( apply_filters( 'give_count_payment_notes_in_comments', false ) ) {
			return $stats;
		}

		$stats = Give_Cache::get_group( "comments-{$post_id}", 'counts' );

		// Return result from cache.
		if ( ! is_null( $stats ) ) {
			return $stats;
		}

		$where = 'WHERE';

		foreach ( $this->comment_types as $index => $comment_type ) {
			$where .= ( $index ? ' AND ' : ' ' ) . "comment_type != \"{$comment_type}\"";
		}

		if ( $post_id > 0 ) {
			$where .= $wpdb->prepare( ' AND comment_post_ID = %d', $post_id );
		}

		$count = $wpdb->get_results(
			"
				  SELECT comment_approved, COUNT( * ) AS num_comments
				  FROM {$wpdb->comments} {$where}
				  GROUP BY comment_approved
				  ",
			ARRAY_A
		);

		$total    = 0;
		$approved = array(
			'0'            => 'moderated',
			'1'            => 'approved',
			'spam'         => 'spam',
			'trash'        => 'trash',
			'post-trashed' => 'post-trashed',
		);

		foreach ( (array) $count as $row ) {
			// Don't count post-trashed toward totals.
			if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
				$total += $row['num_comments'];
			}
			if ( isset( $approved[ $row['comment_approved'] ] ) ) {
				$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
			}
		}

		$stats['total_comments'] = $stats['all'] = $total;
		foreach ( $approved as $key ) {
			if ( empty( $stats[ $key ] ) ) {
				$stats[ $key ] = 0;
			}
		}

		$stats = (object) $stats;

		Give_Cache::set_group( "comments-{$post_id}", $stats, 'counts' );

		return $stats;
	}

	/**
	 * Get donor name
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param string     $author
	 * @param int        $comment_id
	 * @param WP_Comment $comment
	 *
	 * @return mixed
	 */
	public function __get_comment_author( $author, $comment_id, $comment ) {
		if ( in_array( $comment->comment_type, $this->comment_types ) ) {
			switch ( $comment->comment_type ) {
				case 'give_payment_note':
					if ( get_comment_meta( $comment_id, '_give_donor_id', true ) ) {
						$author = give_get_donor_name_by( $comment->comment_post_ID );
					}
			}
		}

		return $author;
	}


	/**
	 * Get comment types
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array @comment_types
	 *
	 * @return array
	 */
	public static function get_comment_types( $comment_types ) {
		$_comment_types = array();
		foreach ( $comment_types as $comment_type ) {
			$_comment_types[] = "give_{$comment_type}_note";
		}

		return $_comment_types;
	}

	/**
	 * Get comments
	 * Note: This function add backward compatibility for get function
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param array $comment_args
	 *
	 * @return array|null
	 */
	private static function _bc_get( $comment_args ) {
		$comments = null;

		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$id           = $comment_args[0];
			$comment_type = $comment_args[1];
			$comment_args = isset( $comment_args[2] ) ? $comment_args[2] : array();
			$search       = isset( $comment_args[3] ) ? $comment_args[3] : '';

			// Set default meta_query value.
			if ( ! isset( $comment_args['meta_query'] ) ) {
				$comment_args['meta_query'] = array();
			}

			// Bailout
			if ( empty( $id ) || empty( $comment_type ) ) {
				return $comments;
			}

			remove_action( 'pre_get_comments', array( self::$instance, 'hide_comments' ), 10 );
			remove_filter( 'comments_clauses', array( self::$instance, 'hide_comments_pre_wp_41' ), 10 );

			switch ( $comment_type ) {
				case 'payment':
					$comment_args['meta_query'] = ! empty( $comment_args['meta_query'] )
						? $comment_args['meta_query']
						: array(
							array(
								'key'     => '_give_donor_id',
								'compare' => 'NOT EXISTS',
							),
						);

					$comments = get_comments(
						wp_parse_args(
							$comment_args,
							array(
								'post_id' => $id,
								'order'   => 'ASC',
								'search'  => $search,
								'type'    => 'give_payment_note',
							)
						)
					);
					break;

				case 'donor':
					$comment_args['meta_query'] = ! empty( $comment_args['meta_query'] )
						? $comment_args['meta_query']
						: array(
							array(
								'key'   => "_give_{$comment_type}_id",
								'value' => $id,
							),
						);

					$comments = get_comments(
						wp_parse_args(
							$comment_args,
							array(
								'order'  => 'ASC',
								'search' => $search,
								'type'   => 'give_donor_note',
							)
						)
					);
					break;
			}

			add_action( 'pre_get_comments', array( self::$instance, 'hide_comments' ), 10, 1 );
			add_filter( 'comments_clauses', array( self::$instance, 'hide_comments_pre_wp_41' ), 10, 1 );
		}

		return $comments;
	}


	/**
	 * Insert/Update comment
	 * Note: This function add backward compatibility for add function
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param array $comment_args Comment arguments.
	 *
	 * @return int|null|WP_Error
	 */
	private static function _bc_add( $comment_args = array() ) {
		$comment_id = null;

		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$id           = $comment_args[0];
			$note         = $comment_args[1];
			$comment_type = $comment_args[2];
			$comment_args = isset( $comment_args[3] ) ? $comment_args[3] : array();

			// Bailout
			if ( empty( $id ) || empty( $note ) || empty( $comment_type ) ) {
				return new WP_Error( 'give_invalid_required_param', __( 'This comment has invalid ID or comment text or comment type', 'give' ) );
			}

			$is_existing_comment = array_key_exists( 'comment_ID', $comment_args ) && ! empty( $comment_args['comment_ID'] );
			$action_type         = $is_existing_comment ? 'update' : 'insert';

			/**
			 * Fires before inserting/updating payment|donor comment.
			 *
			 * @param int    $id   Payment|Donor ID.
			 * @param string $note Comment text.
			 *
			 * @since 1.0
			 */
			do_action( "give_pre_{$action_type}_{$comment_type}_note", $id, $note );

			$comment_args = wp_parse_args(
				$comment_args,
				array(
					'comment_post_ID'      => $id,
					'comment_content'      => $note,
					'user_id'              => is_admin() ? get_current_user_id() : 0,
					'comment_date'         => current_time( 'mysql' ),
					'comment_date_gmt'     => current_time( 'mysql', 1 ),
					'comment_approved'     => 1,
					'comment_parent'       => 0,
					'comment_author'       => '',
					'comment_author_IP'    => '',
					'comment_author_url'   => '',
					'comment_author_email' => '',
					'comment_type'         => "give_{$comment_type}_note",

				)
			);

			// Check comment max length.
			$error = wp_check_comment_data_max_lengths( $comment_args );
			if ( is_wp_error( $error ) ) {
				return $error;
			}

			// Remove moderation emails when comment posted.
			remove_action( 'comment_post', 'wp_new_comment_notify_moderator' );
			remove_action( 'comment_post', 'wp_new_comment_notify_postauthor' );

			// Remove comment flood check.
			remove_action( 'check_comment_flood', 'check_comment_flood_db', 10 );

			$comment_id = $is_existing_comment
				? wp_update_comment( $comment_args )
				: wp_new_comment( $comment_args, true );

			// Add moderation emails when comment posted.
			add_action( 'comment_post', 'wp_new_comment_notify_moderator' );
			add_action( 'comment_post', 'wp_new_comment_notify_postauthor' );

			// Add comment flood check.
			add_action( 'check_comment_flood', 'check_comment_flood_db', 10, 4 );

			update_comment_meta( $comment_id, "_give_{$comment_type}_id", $id );

			/**
			 * Fires after payment|donor comment inserted/updated.
			 *
			 * @param int    $comment_id Comment ID.
			 * @param int    $id         Payment|Donor ID.
			 * @param string $note       Comment text.
			 *
			 * @since 1.0
			 */
			do_action( "give_{$action_type}_{$comment_type}_note", $comment_id, $id, $note );
		}

		return $comment_id;
	}

	/**
	 * Delete comment
	 * Note: This function add backward compatibility for delete function
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param array $comment_args Comment arguments.
	 *
	 * @since  1.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	private static function _bc_delete( $comment_args ) {
		$ret = null;

		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$comment_id   = $comment_args[0];
			$id           = $comment_args[1];
			$comment_type = $comment_args[2];

			$ret = false;

			// Bailout
			if ( empty( $id ) || empty( $comment_id ) || empty( $comment_type ) ) {
				return $ret;
			}
			/**
			 * Fires before deleting donation note.
			 *
			 * @param int $comment_id Comment ID.
			 * @param int $id         Payment|Donor ID.
			 *
			 * @since 1.0
			 */
			do_action( "give_pre_delete_{$comment_type}_note", $comment_id, $id );

			$ret = wp_delete_comment( $comment_id, true );

			/**
			 * Fires after donation note deleted.
			 *
			 * @param int  $comment_id Note ID.
			 * @param int  $id         Payment|Donor ID.
			 * @param bool $ret        Flag to check if comment deleted or not.
			 *
			 * @since 1.0
			 */
			do_action( "give_post_delete_{$comment_type}_note", $comment_id, $id, $ret );
		}

		return $ret;
	}
}
