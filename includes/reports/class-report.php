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
abstract class Report {

	/**
	 * Variables used to register block type
	 *
	 * @var string
	 */
    protected $period = [];

	/**
	 * Initialize.
	 */
	public function __construct($args) {
        $this->period = $args['period'];
    }

	public function register_report () {
        
    }

    public function get_report_object () {
        
    }

    public function get_total () {

    }

    public function get_totals ($args) {

    }

    public function get_average () {

    }

    public function get_count () {

    }

    public function get_counts () {

    }

}
