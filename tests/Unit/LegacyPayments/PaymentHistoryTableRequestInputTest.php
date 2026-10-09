<?php

namespace Give\Tests\Unit\LegacyPayments;

use Give\Tests\TestCase;
use Give_Payment_History_Table;

/**
 * @since TBD
 */
final class PaymentHistoryTableRequestInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // add_query_arg() reads REQUEST_URI, which an earlier test in a full run can leave unset.
        $_SERVER['REQUEST_URI'] = '/wp-admin/edit.php?post_type=give_forms&page=give-payment-history';

        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['s'], $_GET['orderby'], $_GET['paged'], $_GET['status']);
        remove_all_filters('give_payment_table_payments_query');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testPaymentsDataUnslashesAndSanitizesTheFilters(): void
    {
        $_GET['s'] = '<b>O\\\'Brien</b>';
        $_GET['orderby'] = 'ID';
        $_GET['paged'] = '3abc';

        $captured = [];
        add_filter('give_payment_table_payments_query', function ($args) use (&$captured) {
            $captured = $args;

            return ['number' => 1, 'post__in' => [0]];
        });

        (new Give_Payment_History_Table())->payments_data();

        $this->assertSame("O'Brien", $captured['s']);
        $this->assertSame('ID', $captured['orderby']);
        $this->assertSame(3, $captured['page']);
    }

    /**
     * @since TBD
     */
    public function testPaymentsDataLeavesPageEmptyWhenNoneIsSent(): void
    {
        $captured = [];
        add_filter('give_payment_table_payments_query', function ($args) use (&$captured) {
            $captured = $args;

            return ['number' => 1, 'post__in' => [0]];
        });

        (new Give_Payment_History_Table())->payments_data();

        $this->assertNull($captured['page']);
        $this->assertNull($captured['s']);
    }

    /**
     * @since TBD
     */
    public function testGetViewsReadsTheSanitizedStatus(): void
    {
        $_GET['status'] = 'pending<script>';

        $views = (new Give_Payment_History_Table())->get_views();

        $this->assertArrayHasKey('pending', $views);
        $this->assertStringContainsString('current', $views['pending']);
    }
}
