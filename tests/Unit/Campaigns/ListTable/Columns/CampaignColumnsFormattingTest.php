<?php

namespace Give\Tests\Unit\Campaigns\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\Campaigns\ListTable\Columns\DateColumn;
use Give\Campaigns\ListTable\Columns\DonationsCountColumn;
use Give\Campaigns\ListTable\Columns\EndDateColumn;
use Give\Campaigns\ListTable\Columns\GoalColumn;
use Give\Campaigns\ListTable\Columns\RevenueColumn;
use Give\Campaigns\ListTable\Columns\StartDateColumn;
use Give\Campaigns\ListTable\Columns\StatusColumn;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class CampaignColumnsFormattingTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        update_option('date_format', 'd/m/Y');
        update_option('time_format', 'g:i a');
        update_option('timezone_string', 'UTC');
        foreach (
            [
                'currency' => 'USD',
                'currency_position' => 'before',
                'thousands_separator' => ',',
                'decimal_separator' => '.',
                'number_decimals' => 2,
                'auto_format_currency' => 'on',
            ] as $key => $value
        ) {
            give_update_option($key, $value);
        }
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['date_format', 'time_format', 'timezone_string'] as $option) {
            delete_option($option);
        }

        foreach (['currency', 'currency_position', 'thousands_separator', 'decimal_separator', 'number_decimals', 'auto_format_currency'] as $key) {
            give_delete_option($key);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testRevenueUsesTheSharedMoneyFormatAndHasARawValue(): void
    {
        $campaign = new Campaign(['id' => 12]);
        $column = $this->columnWithData(new RevenueColumn(), $this->dataStub(1331.28));

        $this->assertStringContainsString('>$1,331.28</a>', $column->getCellValue($campaign, 'pt_BR'));
        $this->assertSame(['amount' => '1331.28', 'currency' => 'USD'], $column->getRawValue($campaign));
        $this->assertSame('money', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testRevenueRawValueRunsNoQuery(): void
    {
        $campaign = new Campaign(['id' => 12]);
        $column = $this->columnWithData(new RevenueColumn(), $this->dataStub(5.5));

        $before = get_num_queries();
        $column->getCellValue($campaign);
        $column->getRawValue($campaign);

        $this->assertSame(0, get_num_queries() - $before);
    }

    /**
     * @since TBD
     */
    public function testCreatedDateFollowsTheSiteSettings(): void
    {
        $campaign = new Campaign(['createdAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC'))]);
        $column = new DateColumn();

        $this->assertSame('29/11/2025 at 4:16 pm', $column->getCellValue($campaign));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($campaign));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testStartDateIsDateOnlyInTheSiteFormat(): void
    {
        $campaign = new Campaign(['startDate' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC'))]);
        $column = new StartDateColumn();

        $this->assertSame('29/11/2025', $column->getCellValue($campaign));
        $this->assertStringNotContainsString(' at ', $column->getCellValue($campaign));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($campaign));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testEndDateIsDateOnlyInTheSiteFormat(): void
    {
        $campaign = new Campaign(['endDate' => new DateTime('2026-01-05 09:00:00', new DateTimeZone('UTC'))]);
        $column = new EndDateColumn();

        $this->assertSame('05/01/2026', $column->getCellValue($campaign));
        $this->assertSame('2026-01-05T09:00:00Z', $column->getRawValue($campaign));
    }

    /**
     * @since TBD
     */
    public function testCampaignWithoutAnEndDateShowsTheDash(): void
    {
        $campaign = new Campaign(['endDate' => null]);
        $column = new EndDateColumn();

        $this->assertStringContainsString('givewp-list-table-dash', $column->getCellValue($campaign));
        $this->assertNull($column->getRawValue($campaign));
    }

    /**
     * @since TBD
     */
    public function testCountAndStatusKinds(): void
    {
        $campaign = new Campaign(['id' => 3]);
        $count = $this->columnWithData(new DonationsCountColumn(), $this->dataStub(0, 7));

        $this->assertSame('count', $count->getKind());
        $this->assertSame(7, $count->getRawValue($campaign));
        $this->assertSame('status', (new StatusColumn())->getKind());
        $this->assertSame('text', (new GoalColumn())->getKind());
    }

    /**
     * @since TBD
     */
    public function testAmountGoalTextUsesTheSharedMoneyFormat(): void
    {
        $campaign = new Campaign(['id' => 5, 'goal' => 500, 'goalType' => CampaignGoalType::AMOUNT()]);
        $stub = $this->getMockBuilder(CampaignsDataRepository::class)
            ->onlyMethods(['getRevenue', 'getDonationsCount'])
            ->getMock();
        $stub->method('getRevenue')->willReturn(125.0);

        $goal = $this->callGoalData($stub, $campaign);

        $this->assertSame('$500.00', $goal['goalFormatted']);
        $this->assertSame('$125.00', $goal['actualFormatted']);
    }

    /**
     * @since TBD
     */
    public function testCampaignWithoutAGoalShowsTheLabeledDash(): void
    {
        $campaign = new Campaign(['id' => 5, 'goal' => 0, 'goalType' => CampaignGoalType::AMOUNT()]);
        $stub = $this->getMockBuilder(CampaignsDataRepository::class)
            ->onlyMethods(['getGoalData'])
            ->getMock();
        $stub->method('getGoalData')->willReturn(['goal' => 0]);

        $cell = $this->columnWithData(new GoalColumn(), $stub)->getCellValue($campaign);

        $this->assertStringContainsString('givewp-list-table-dash', $cell);
        $this->assertStringContainsString('No goal set', $cell);
        $this->assertStringNotContainsString('No Goal Set', $cell);
    }

    /**
     * @since TBD
     */
    public function testCountGoalIsNotFormattedAsMoney(): void
    {
        $campaign = new Campaign(['id' => 5, 'goal' => 40, 'goalType' => CampaignGoalType::DONATIONS()]);
        $stub = $this->getMockBuilder(CampaignsDataRepository::class)
            ->onlyMethods(['getDonationsCount'])
            ->getMock();
        $stub->method('getDonationsCount')->willReturn(10);

        $goal = $this->callGoalData($stub, $campaign);

        $this->assertSame(40, $goal['goalFormatted']);
        $this->assertSame(10, $goal['actualFormatted']);
    }

    /**
     * @since TBD
     */
    private function callGoalData(CampaignsDataRepository $repository, Campaign $campaign): array
    {
        return $repository->getGoalData($campaign, true);
    }

    /**
     * @since TBD
     */
    private function dataStub(float $revenue, int $donations = 0): CampaignsDataRepository
    {
        $stub = $this->getMockBuilder(CampaignsDataRepository::class)
            ->onlyMethods(['getRevenue', 'getDonationsCount'])
            ->getMock();
        $stub->method('getRevenue')->willReturn($revenue);
        $stub->method('getDonationsCount')->willReturn($donations);

        return $stub;
    }

    /**
     * @since TBD
     *
     * @template T of \Give\Framework\ListTable\ModelColumn
     *
     * @param T $column
     *
     * @return T
     */
    private function columnWithData($column, CampaignsDataRepository $data)
    {
        $column->setListTableData($data);

        return $column;
    }
}
