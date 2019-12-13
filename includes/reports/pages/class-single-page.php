<?php
/**
 * Single Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality and actions specific to the single page
 */
class Single_Page extends Page {

    public function __construct() {

        require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

        $this->title = 'Single';
        $this->show_in_menu = false;
        $this->path = '/single';
        $this->cards = [];
    }
}
