<?php

namespace Give\Tests\Unit\Framework\Models;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.18.0
 */
final class ModelQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.18.0
     */
    public function testCountReturnsRowCountWithoutGroupBy()
    {
        $campaigns = Campaign::factory()->count(3)->create();

        $this->assertSame(count($campaigns), Campaign::query()->count());
    }

    /**
     * @since 4.18.0
     */
    public function testCountReturnsGroupCountWithGroupBy()
    {
        $campaigns = Campaign::factory()->count(3)->create();

        /*
         * Grouping on the primary key makes every row its own group, which is the case that used
         * to collapse to the first group's row count.
         */
        $this->assertSame(count($campaigns), Campaign::query()->groupBy('id')->count());
    }

    /**
     * @since 4.18.0
     */
    public function testCountReturnsDistinctGroupCountWhenRowsShareAGroup()
    {
        $statuses = [CampaignStatus::ACTIVE(), CampaignStatus::DRAFT()];

        /*
         * Every group is larger than the number of groups, so returning any single group's row
         * count cannot coincide with the expected answer.
         */
        foreach ($statuses as $index => $status) {
            Campaign::factory()->count($index + count($statuses) + 1)->create(['status' => $status]);
        }

        $this->assertSame(count($statuses), Campaign::query()->groupBy('status')->count());
    }

    /**
     * @since 4.18.0
     */
    public function testCountIncludesTheGroupOfRowsWithANullGroupedColumn()
    {
        $endDates = [null, Temporal::withoutMicroseconds(Temporal::getCurrentDateTime())];

        foreach ($endDates as $endDate) {
            Campaign::factory()->count(2)->create(['endDate' => $endDate]);
        }

        $this->assertSame(count($endDates), Campaign::query()->groupBy('end_date')->count());
    }

    /**
     * @since 4.18.0
     */
    public function testCountWithColumnCountsNonNullValuesWithinGroups()
    {
        $nonNullEndDates = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        Campaign::factory()->count(2)->create(['endDate' => null]);
        Campaign::factory()->count(2)->create(['endDate' => $nonNullEndDates]);

        $query = Campaign::query()->groupBy('status');

        $this->assertSame(1, $query->count());
        $this->assertSame(4, $query->count('id'));
        $this->assertSame(2, $query->count('end_date'));
    }

    /**
     * @since 4.18.0
     */
    public function testCountKeepsSelectAliasesReferencedByHaving()
    {
        Campaign::factory()->count(3)->create(['status' => CampaignStatus::ACTIVE()]);
        Campaign::factory()->count(1)->create(['status' => CampaignStatus::DRAFT()]);

        /*
         * The HAVING filters out every group whose aggregate alias drops below the threshold, so
         * only the active group survives and the count must reflect the surviving groups rather
         * than fail on the alias the grouped count's select list would otherwise drop.
         */
        $this->assertSame(
            1,
            Campaign::query()
                ->select(['COUNT(id)', 'total'])
                ->groupBy('status')
                ->having('total', '>', 2)
                ->count()
        );
    }

    /**
     * @since 4.20.0
     */
    public function testUsingACloneLeavesTheOriginalQueryUnchanged()
    {
        Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE()]);

        $query = Donation::query()->where('post_status', DonationStatus::COMPLETE);
        $sql = $query->getSQL();

        /*
         * clone copies the builder and shares the where, select and join objects it holds, so
         * whatever a copy does with them must not reach the query it was made from.
         */
        $counted = clone $query;
        $counted->where('give_donationmeta_attach_meta_mode.meta_value', DonationMode::TEST)->count();

        $paged = clone $query;
        $paged->limit(1)->offset(1)->orderBy('post_date', 'DESC')->getAll();

        $this->assertSame($sql, $query->getSQL());
    }

    /**
     * @since 4.20.0
     */
    public function testPagingTheOriginalQueryLeavesItsCloneCountingEveryRow()
    {
        $donations = Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE()]);

        $query = Donation::query()->where('post_status', DonationStatus::COMPLETE);
        $totalQuery = clone $query;
        $totalSql = $totalQuery->getSQL();

        $page = $query->limit(2)->offset(0)->orderBy('post_date', 'DESC')->getAll();

        $this->assertSame($totalSql, $totalQuery->getSQL());
        $this->assertCount(2, $page);
        $this->assertSame(count($donations), $totalQuery->count());
    }
}
