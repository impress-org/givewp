<?php

namespace Give\Tests\Unit\LegacyReports;

use Give\Tests\TestCase;

/**
 * @since 4.19.1
 */
final class ReportsRequestInputTest extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/reports.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/graphing.php';
    }

    /**
     * @since 4.19.1
     */
    public function tearDown(): void
    {
        unset($_GET['view'], $_GET['range'], $_GET['year'], $_GET['day']);

        parent::tearDown();
    }

    /**
     * @since 4.19.1
     */
    public function testReportingViewAcceptsAKnownView(): void
    {
        $_GET['view'] = 'gateways';

        $this->assertSame('gateways', give_get_reporting_view());
    }

    /**
     * @since 4.19.1
     */
    public function testReportingViewFallsBackForAnUnknownView(): void
    {
        $_GET['view'] = '<b>nope</b>';

        $this->assertSame('earnings', give_get_reporting_view());
    }

    /**
     * @since 4.19.1
     */
    public function testReportDatesStripTagsFromTheRequest(): void
    {
        $_GET['range'] = 'other';
        $_GET['year'] = '2024<script>';
        $_GET['day'] = '7';

        $dates = give_get_report_dates();

        $this->assertSame('other', $dates['range']);
        $this->assertSame('2024', $dates['year']);
        $this->assertSame('7', $dates['day']);
    }
}
