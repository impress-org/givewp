<?php
/**
 * Donors Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality and actions specific to the donors page
 */
class Donors_Page extends Page {

    public function __construct() {

        require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

        $this->title = 'Donors';
        $this->path = '/donors';
        $this->cards = [];
    }
}
