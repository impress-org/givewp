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
abstract class Page {

	/**
	 * Variables used to register block type
	 *
	 * @var string
	 */
    protected $title = '';
    protected $show_in_menu = true;
    protected $path = '';
    protected $charts = [];

	/**
	 * Initialize.
	 */
	public function __construct() {
        //Do nothing
    }

    public function get_page_object() {

        $charts = [];
        foreach ($this->charts as $slug => $class) {
            $charts[$slug] = $class->get_chart_object();
        }

        $object = [
            'title' => $this->title,
            'show_in_menu' => $this->show_in_menu,
            'path' => $this->path,
            'charts' => $charts
        ];

        return $object;

    }

	public function handle_api_callback ($data) {
        $response = new WP_REST_Response([
            'key' => 'value',
            'page' => 'data'
        ]);
        return $response;
    }

}
