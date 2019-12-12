<?php
/**
 * Card class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

class Card {

	/**
	 * Variables used to register block type
	 *
	 * @var string
	 */
    protected $title = '';
    protected $type = '';
    protected $width = 6;
    protected $props = [];

	/**
	 * Initialize.
	 */
	public function __construct($args) {

        $this->title = $args['title'];
        $this->type = $args['type'];
        $this->width = $args['width'];
        $this->props = $args['props'];

    }

    public function get_card_object () {
        $object = [
            'title' => $this->title,
            'type' => $this->type,
            'width' => $this->width,
            'props' => $this->props
        ];
        return $object;
    }

}
