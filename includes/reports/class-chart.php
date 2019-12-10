<?php
/**
 * Common functionality reports
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Common functionality for admin screens. Override this class.
 */
abstract class Chart {

	/**
	 * Variables used to register block type
	 *
	 * @var string
	 */
    protected $title = '';
    protected $type = '';
    protected $width = 6;
    protected $data = [];

	/**
	 * Initialize.
	 */
	public function __construct($args) {

        $this->title = $args['title'];
        $this->$type = $args['type'];
        $this->$width = $args['width'];
        $this->data = $args['data'];

    }

	public function register_chart () {
        
    }

    public function get_chart_object () {
        
    }

}
