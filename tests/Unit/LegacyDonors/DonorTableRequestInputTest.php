<?php

namespace Give\Tests\Unit\LegacyDonors;

use Give\Tests\TestCase;
use Give_Donor_List_Table;

/**
 * @since TBD
 */
final class DonorTableRequestInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/class-donor-table.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['order'], $_GET['orderby'], $_GET['paged'], $_GET['s']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testQueryUnslashesAndSanitizesTheSortValues(): void
    {
        $_GET['order'] = 'ASC<script>';
        $_GET['orderby'] = 'na\\me';

        $args = (new Give_Donor_List_Table())->get_donor_query();

        $this->assertSame('ASC', $args['order']);
        $this->assertSame('name', $args['orderby']);
    }

    /**
     * @since TBD
     */
    public function testQueryCastsThePageToAnInteger(): void
    {
        $_GET['paged'] = '3abc';

        $args = (new Give_Donor_List_Table())->get_donor_query();

        $this->assertSame(3, $args['page']);
    }

    /**
     * @since TBD
     */
    public function testSearchUnslashesTheTerm(): void
    {
        $_GET['s'] = 'O\\\'Brien';

        $this->assertSame("O'Brien", (new Give_Donor_List_Table())->get_search());
    }
}
