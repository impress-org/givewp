<?php

/**
 * Class for managing tooltips
 *
 * @package     Give
 * @subpackage  Classes/Give_Tooltips
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */
class Give_Tooltips {
	/**
	 * Set tooltip arguments.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param $args
	 *
	 * @return array
	 */
	private function set_toottip_args( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				// Tooltip tag.
				'tag'         => 'span',
				'tag_content' => '',

				// Set to link of anchor if tooltip tag is anchor.
				'link'        => '#',

				// Text for tooltip
				'label'       => '',

				// Value: top-right, top, top-left, right, left, bottom-right, bottom, bottom-left.
				'position'    => 'top',

				// Value: error, warning, info, success.
				'status'      => '',

				// Value: small, medium, large.
				'size'        => '',

				// Value: true/false.
				'show_always' => false,

				// Value: true/false
				'round_edges' => false,

				// Value: true/false
				'animate'     => true,

				// Attributes.
				'attributes'  => array(),

				// Value: true/false
				'auto_width'  => true,
			)
		);

		// Auto set width of tooltip.
		if (
			! empty( $args['auto_width'] ) &&
			! empty( $args['label'] ) &&
			empty( $args['size'] )
		) {
			if ( 15 < str_word_count( $args['label'] ) ) {
				$args['size'] = 'large';
			} elseif ( 7 < str_word_count( $args['label'] ) ) {
				$args['size'] = 'medium';
			}
		}

		return $args;
	}


	/**
	 * Render tooltip
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function render( $args ) {
		$args = $this->set_toottip_args( $args );

		$tooltip_pos = array(
			'top'          => 'hint--top',
			'top-right'    => 'hint--top-right',
			'top-left'     => 'hint--top-left',
			'right'        => 'hint--right',
			'left'         => 'hint--left',
			'bottom'       => 'hint--bottom',
			'bottom-right' => 'hint--bottom-right',
			'bottom-left'  => 'hint--bottom-left',
		);

		$tooltip_status = array(
			'error'   => 'hint--error',
			'warning' => 'hint--warning',
			'info'    => 'hint--info',
			'success' => 'hint--success',
		);

		$tooltip_size = array(
			'small'  => 'hint--small',
			'medium' => 'hint--medium',
			'large'  => 'hint--large',
		);

		// Set label.
		$args['attributes']['aria-label'] = $args['label'];

		// Set classes.
		$args['attributes']['class'] = ! empty( $args['attributes']['class'] ) ? $args['attributes']['class'] : '';
		$args['attributes']['class'] .= " {$tooltip_pos[ $args['position'] ]}";
		$args['attributes']['class'] .= ! empty( $args['status'] ) ? " {$tooltip_status[ $args['status'] ]}" : '';
		$args['attributes']['class'] .= ! empty( $args['size'] ) ? " {$tooltip_size[ $args['size'] ]}" : '';
		$args['attributes']['class'] .= $args['show_always'] ? ' hint--always' : '';
		$args['attributes']['class'] .= $args['round_edges'] ? ' hint--rounded' : '';
		$args['attributes']['class'] .= $args['animate'] ? ' hint--bounce' : ' hint--no-animate';
		$args['attributes']['class'] = trim( $args['attributes']['class'] );

		// Set link attribute in tooltip has anchor tag.
		if ( 'a' === $args['tag'] && ! empty( $args['link'] ) ) {
			$args['attributes']['href'] = esc_url( $args['link'] );
		}

		return sprintf( '<%1$s %2$s rel="tooltip">%3$s</%1$s>', $args['tag'], give_get_attribute_str( $args['attributes'] ), $args['tag_content'] );
	}


	/**
	 * Render tooltip with anchor tag
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function render_link( $args ) {
		$args['tag']    = 'a';
		$tooltip_markup = $this->render( $args );

		return $tooltip_markup;
	}

	/**
	 * Render tooltip with span tag
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array|string $args
	 *
	 * @return string
	 */
	function render_span( $args ) {
		// Set tooltip args from string.
		if ( is_string( $args ) ) {
			$args = array( 'label' => $args );
		}

		$args['tag']    = 'span';
		$tooltip_markup = $this->render( $args );

		return $tooltip_markup;
	}

	/**
	 * Render tooltip with span tag and question mark icon
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array|string $args
	 *
	 * @return string
	 */
	function render_help( $args ) {
		// Set tooltip args from string.
		if ( is_string( $args ) ) {
			$args = array( 'label' => $args );
		}

		$args['tag_content']         = '<i class="give-icon give-icon-question"></i>';
		$args['attributes']['class'] = 'give-tooltip';
		$tooltip_markup              = $this->render_span( $args );

		return $tooltip_markup;
	}
}
