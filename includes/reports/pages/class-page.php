<?php
/**
 * Abstract page class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Common functionality for pages. Override this class.
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
    protected $cards = [];

	/**
	 * Initialize.
	 */
	public function __construct() {
        //Do nothing
    }

    public function get_page_object() {

        $cards = [];
        foreach ($this->cards as $slug => $class) {
            $cards[$slug] = $class->get_card_object();
        }

        $object = [
            'title' => $this->title,
            'show_in_menu' => $this->show_in_menu,
            'path' => $this->path,
            'cards' => $cards
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
