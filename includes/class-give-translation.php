<?php

use Give\Log\Log;

/**
 * Translations
 *
 * @package     Give
 * @subpackage  Classes/Give_Stats
 * @copyright   Copyright (c) 2017, Give
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */
class Give_Translations {
	/**
	 * Instance.
	 *
	 * @since  2.0
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Text config.
	 *
	 * @since  2.0
	 * @access private
	 * @var
	 */
	private static $text_configs = array();

	/**
	 * Translated texts.
	 *
	 * @since  2.0
	 * @access private
	 * @var
	 */
	private static $text_translations = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.0
	 * @access public
	 * @return static
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup
	 *
	 * @since  2.0
	 * @access public
	 */
	public function setup() {
		self::setup_hooks();
	}

	/**
	 * Setup hooks
	 *
	 * @since  2.0
	 * @access public
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'load_translated_texts' ), 999 );
	}

	/**
	 * Load translated texts.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function load_translated_texts() {
		/**
		 * Filter the translated texts.
		 *
		 * @since 2.0
		 */
		self::$text_translations = apply_filters(
			'give_translated_texts',
			self::$text_translations
		);
	}

	/**
	 * Add text by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error false on success otherwise WP_Error object
	 */
	public static function add_text( $args = array() ) {
		$error = false;

		// Set text params.
		$args = wp_parse_args(
			$args,
			array(
				'text'  => '',
				'id'    => '',
				'group' => '',
				'type'  => 'text',
			)
		);

		try {
			// Check for errors.
			if ( empty( $args['text'] ) ) {
				/* @var WP_Error $error */
				$error = new WP_Error( 'EMPTY_TEXT', __( 'Empty string is not allowed.', 'give' ), $args );
				throw new Exception( $error->get_error_message( 'EMPTY_TEXT' ) );
			} elseif ( empty( $args['id'] ) ) {
				/* @var WP_Error $error */
				$error = new WP_Error( 'EMPTY_ID', __( 'Empty ID is not allowed.', 'give' ), $args );
				throw new Exception( $error->get_error_message( 'EMPTY_ID' ) );

			} elseif (
				empty( $args['group'] ) &&
				array_key_exists( $args['id'], self::$text_configs )
			) {
				/* @var WP_Error $error */
				$error = new WP_Error( 'TEXT_ID_ALREADY_EXIST', __( 'Text ID without a group already exists.', 'give' ), $args );
				throw new Exception( $error->get_error_message( 'TEXT_ID_ALREADY_EXIST' ) );

			} elseif (
				! empty( $args['group'] ) &&
				! empty( self::$text_configs[ $args['group'] ] ) &&
				array_key_exists( $args['id'], self::$text_configs[ $args['group'] ] )
			) {
				/* @var WP_Error $error */
				$error = new WP_Error( 'TEXT_ID_WITHIN_GROUP_ALREADY_EXIST', __( 'Text ID within a group already exists.', 'give' ), $args );
				throw new Exception( $error->get_error_message( 'TEXT_ID_WITHIN_GROUP_ALREADY_EXIST' ) );

			}

			// Add text.
			if ( ! empty( $args['group'] ) ) {
				self::$text_configs[ $args['group'] ][ $args['id'] ] = $args;
			} else {
				self::$text_configs[ $args['id'] ] = $args;
			}
		} catch ( Exception $e ) {
            Log::error( $e->getMessage() );
        }// End try().

		/**
		 * Filter the texts
		 *
		 * @since 2.0
		 */
		self::$text_configs = apply_filters( 'give_texts', self::$text_configs );

		return $error;
	}

	/**
	 * Add label by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function add_label( $args = array() ) {
		// Set text params.
		$args = wp_parse_args(
			$args,
			array(
				'text'  => '',
				'id'    => '',
				'group' => '',
			)
		);

		$args['type'] = 'label';
		$args['id']   = "{$args['id']}_label";

		return self::add_text( $args );
	}

	/**
	 * Add tooltip by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function add_tooltip( $args = array() ) {
		// Set text params.
		$args = wp_parse_args(
			$args,
			array(
				'text'  => '',
				'id'    => '',
				'group' => '',
			)
		);

		$args['type'] = 'tooltip';
		$args['id']   = "{$args['id']}_tooltip";

		return self::add_text( $args );
	}

	/**
	 * Add translation by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array 4args
	 *
	 * @return string
	 */
	public static function add_translation( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'    => '',
				'group' => '',
				'text'  => '',
			)
		);

		// Bailout.
		if ( empty( $args['id'] ) ) {
			return;
		}

		if ( ! empty( $args['group'] ) ) {
			self::$text_translations[ $args['group'] ][ $args['id'] ] = $args['text'];
		} else {
			self::$text_translations[ $args['id'] ] = $args['text'];
		}
	}

	/**
	 * Get label translation by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 * @param string $text
	 *
	 * @return string
	 */
	public static function add_label_translation( $id, $group = '', $text = '' ) {
		return self::get_text(
			array(
				'id'    => "{$id}_label",
				'group' => $group,
				'text'  => $text,
			)
		);
	}

	/**
	 * Get tooltip translation by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 * @param string $text
	 *
	 * @return string
	 */
	public static function add_tooltip_translation( $id, $group = '', $text = '' ) {
		return self::get_text(
			array(
				'id'    => "{$id}_label",
				'group' => $group,
				'text'  => $text,
			)
		);
	}

	/**
	 * Get label by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 *
	 * @return string
	 */
	public static function get_label( $id, $group = '' ) {
		return self::get_text(
			array(
				'id'    => "{$id}_label",
				'group' => $group,
				'type'  => 'label',
			)
		);
	}

	/**
	 * Get tooltip by group ( if any )
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $id
	 * @param string $group
	 *
	 * @return string
	 */
	public static function get_tooltip( $id, $group = '' ) {
		return self::get_text(
			array(
				'id'    => "{$id}_tooltip",
				'group' => $group,
				'type'  => 'tooltip',
			)
		);
	}

	/**
	 * Get text by group
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_text( $args = array() ) {
		$text = '';

		// Bailout.
		if ( empty( $args ) ) {
			return $text;
		}

		// Setup args.
		$args = wp_parse_args(
			$args,
			array(
				'id'    => '',
				'group' => '',
				'type'  => 'text',
			)
		);

		// Check if text exist.
		if (
			empty( $args['id'] ) ||
			( empty( $args['group'] ) && ! array_key_exists( $args['id'], self::$text_configs ) ) ||
			( ! empty( $args['group'] ) && ! empty( self::$text_configs[ $args['group'] ] ) && ! array_key_exists( $args['id'], self::$text_configs[ $args['group'] ] ) )
		) {
			return $text;
		}

		// Get text value.
		if (
			! empty( $args['group'] ) &&
			array_key_exists( $args['group'], self::$text_configs )
		) {
			$text = self::$text_configs[ $args['group'] ][ $args['id'] ]['text'];

			// Get translated text if exist.
			if (
				! empty( self::$text_translations ) &&
				! empty( self::$text_translations[ $args['group'] ] ) &&
				array_key_exists( $args['id'], self::$text_translations[ $args['group'] ] )
			) {
				$text = self::$text_translations[ $args['group'] ][ $args['id'] ];
			}
		} elseif (
			empty( $args['group'] ) &&
			array_key_exists( $args['id'], self::$text_configs )
		) {
			$text = self::$text_configs[ $args['id'] ]['text'];

			// Get translated text if exist.
			if (
				! empty( self::$text_translations ) &&
				array_key_exists( $args['id'], self::$text_translations )
			) {
				$text = self::$text_translations[ $args['id'] ];
			}
		}

		/**
		 * Filter the give text
		 *
		 * @since 2.0
		 */
		$text = apply_filters( 'give_text', $text, $args, self::$text_configs, self::$text_translations );

		return $text;
	}
}

// Setup translations.
Give_Translations::get_instance()->setup();

