<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Donations\Counters\SyncDonationCounters;
use Give\Donations\Models\Donation;
use Give\Donations\Counters\DonationCountersRebuild;
use Give\Donations\Repositories\DonationTotalsCache;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\EventTickets\Models\Event;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The six list tables send their total apart from their rows: `withCount=false` on the list route
 * and a `/count` route. These tests prove the three answers agree for the same filters, and that the
 * rows are the same rows with or without the total.
 *
 * @since TBD
 */
class TestListCountRoutes extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    private const DONATIONS = '/give-api/v2/admin/donations';
    private const DONORS = '/give-api/v2/admin/donors';
    private const SUBSCRIPTIONS = '/give-api/v2/admin/subscriptions';
    private const FORMS = '/give-api/v2/admin/forms';
    private const CAMPAIGNS = '/givewp/v3/campaigns/list-table';
    private const EVENTS = '/give-api/v2/events-tickets/events/list-table';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
        give_update_option('enable_event_tickets', 'enabled');

        Donation::query()->delete();
        DB::query('DELETE FROM ' . DB::prefix('give_subscriptions'));
        DB::query('DELETE FROM ' . DB::prefix('give_donors'));

        // Earlier tests may leave counters and cached totals behind for donations that are gone, and the
        // listener remembers the bucket of every donation id it has seen. The tables are reset between
        // tests, so ids repeat, and a remembered bucket would hide a new donation from the counters.
        delete_option(DonationTotalsCache::OPTION);
        give(SyncDonationCounters::class)->reset();
        (new DonationCountersRebuild(1, 0))();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        give_delete_option('enable_event_tickets');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    private function get(string $route, array $params = []): WP_REST_Response
    {
        $request = new WP_REST_Request('GET', $route);
        $request->set_query_params($params);

        return rest_do_request($request);
    }

    /**
     * Asks for every page of the list, with and without the total, and for the count, and checks
     * that the three agree.
     *
     * @since TBD
     */
    private function assertCountMatchesList(string $route, array $params, int $expectedTotal): void
    {
        $params += ['perPage' => 2, 'locale' => 'en-US'];

        $count = $this->get($route . '/count', $params);
        $this->assertSame(200, $count->get_status());
        $this->assertSame(['totalItems', 'totalPages'], array_keys($count->get_data()));
        $this->assertSame($expectedTotal, $count->get_data()['totalItems']);
        $this->assertSame((int)ceil($expectedTotal / $params['perPage']), $count->get_data()['totalPages']);

        $lastPage = max(1, (int)ceil($expectedTotal / $params['perPage'])) + 1;
        $seenIds = [];

        for ($page = 1; $page <= $lastPage; $page++) {
            $pageParams = $params + ['page' => $page];
            $list = $this->get($route, $pageParams);
            $rows = $this->get($route, $pageParams + ['withCount' => 'false']);

            $this->assertSame(200, $list->get_status());
            $this->assertSame(200, $rows->get_status());

            $listData = $list->get_data();
            $rowsData = $rows->get_data();

            // The list is unchanged, and still has its total.
            $this->assertSame($expectedTotal, $listData['totalItems']);
            $this->assertSame($count->get_data()['totalPages'], $listData['totalPages']);

            // The rows request has the same rows and every other key, and a hasNextPage instead of the totals.
            $this->assertSame($listData['items'], $rowsData['items'], "page $page");
            $this->assertArrayNotHasKey('totalItems', $rowsData);
            $this->assertArrayNotHasKey('totalPages', $rowsData);
            $this->assertSame(
                array_diff_key($listData, ['totalItems' => 1, 'totalPages' => 1]),
                array_diff_key($rowsData, ['hasNextPage' => 1])
            );
            $this->assertSame($page * $params['perPage'] < $expectedTotal, $rowsData['hasNextPage'], "page $page");

            $seenIds = array_merge($seenIds, array_column($rowsData['items'], 'id'));
        }

        $this->assertCount($expectedTotal, array_unique($seenIds), 'The pages together hold every match once.');
    }

    /**
     * @since TBD
     */
    public function testDonationsCountMatchesList(): void
    {
        foreach (['Zed', 'Zed', 'Amy', 'Bob', 'Cat'] as $firstName) {
            Donation::factory()->create([
                'firstName' => $firstName,
                'mode' => DonationMode::LIVE(),
                'status' => DonationStatus::COMPLETE(),
            ]);
        }

        Donation::factory()->create(['mode' => DonationMode::LIVE(), 'status' => DonationStatus::PENDING()]);
        Donation::factory()->create(['mode' => DonationMode::TEST(), 'status' => DonationStatus::COMPLETE()]);
        Donation::factory()->create(['mode' => DonationMode::LIVE(), 'status' => DonationStatus::TRASH()]);

        $live = ['testMode' => 'false'];

        $this->assertCountMatchesList(self::DONATIONS, $live, 6);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['status' => 'publish'], 5);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['status' => 'publish,pending'], 6);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['status' => 'trash'], 1);
        $this->assertCountMatchesList(self::DONATIONS, ['testMode' => 'true'], 1);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['search' => 'Zed'], 2);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['search' => 'Zed', 'status' => 'publish'], 2);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['search' => 'Nobody'], 0);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['donationType' => 'one-time'], 6);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['donationType' => 'recurring'], 0);
    }

    /**
     * @since TBD
     */
    public function testDonationsCountMatchesListForCampaignAndDates(): void
    {
        $campaign = Campaign::factory()->create();
        Donation::factory()->count(3)->create(['campaignId' => $campaign->id, 'mode' => DonationMode::LIVE(), 'status' => DonationStatus::COMPLETE()]);
        $other = Campaign::factory()->create();
        Donation::factory()->count(2)->create(['campaignId' => $other->id, 'mode' => DonationMode::LIVE(), 'status' => DonationStatus::COMPLETE()]);

        $live = ['testMode' => 'false', 'status' => 'publish'];

        $this->assertCountMatchesList(self::DONATIONS, $live + ['campaignId' => (string)$campaign->id], 3);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['start' => '2000-01-01T00:00:00'], 5);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['end' => '2000-01-01T00:00:00'], 0);
        $this->assertCountMatchesList(self::DONATIONS, $live + ['campaignId' => (string)$campaign->id, 'start' => '2000-01-01T00:00:00'], 3);
    }

    /**
     * @since TBD
     */
    public function testDonationsRowsAreNeverBlockedByAnOutdatedTotal(): void
    {
        $live = ['testMode' => 'false', 'status' => 'publish', 'perPage' => 2, 'search' => 'Zed', 'locale' => 'en-US'];
        $make = static function () {
            return Donation::factory()->create([
                'firstName' => 'Zed',
                'mode' => DonationMode::LIVE(),
                'status' => DonationStatus::COMPLETE(),
            ]);
        };

        $make();
        $make();

        // The count is read, and cached, with two matches.
        $this->assertSame(2, $this->get(self::DONATIONS . '/count', $live)->get_data()['totalItems']);

        $third = $make();
        $rows = $this->get(self::DONATIONS, $live + ['page' => 1, 'withCount' => 'false'])->get_data();

        $this->assertSame((string)$third->id, (string)$rows['items'][0]['id']);
        $this->assertTrue($rows['hasNextPage']);

        $secondPage = $this->get(self::DONATIONS, $live + ['page' => 2, 'withCount' => 'false'])->get_data();

        $this->assertCount(1, $secondPage['items']);
        $this->assertFalse($secondPage['hasNextPage']);
    }

    /**
     * A deep page is read from the other end, which needs the total, so the rows response carries it.
     *
     * @since TBD
     */
    public function testDonationsDeepPageWithoutTotalStillHasTheRightRows(): void
    {
        Donation::factory()->count(3)->create(['mode' => DonationMode::LIVE(), 'status' => DonationStatus::COMPLETE()]);

        $params = ['testMode' => 'false', 'status' => 'publish', 'withCount' => 'false', 'perPage' => 10000];

        $past = $this->get(self::DONATIONS, $params + ['page' => 2])->get_data();

        $this->assertSame([], $past['items']);
        $this->assertSame(3, $past['totalItems']);
        $this->assertFalse($past['hasNextPage']);

        $first = $this->get(self::DONATIONS, $params + ['page' => 1])->get_data();

        $this->assertCount(3, $first['items']);
        $this->assertArrayNotHasKey('totalItems', $first);
        $this->assertFalse($first['hasNextPage']);
    }

    /**
     * @since TBD
     */
    public function testDonorsCountMatchesList(): void
    {
        foreach (['Zed', 'Zed', 'Amy', 'Bob', 'Cat'] as $firstName) {
            Donor::factory()->create(['name' => $firstName . ' Test']);
        }

        $this->assertCountMatchesList(self::DONORS, [], 5);
        $this->assertCountMatchesList(self::DONORS, ['search' => 'Zed'], 2);
        $this->assertCountMatchesList(self::DONORS, ['search' => 'Nobody'], 0);
        $this->assertCountMatchesList(self::DONORS, ['donorType' => 'one-time'], 5);
        $this->assertCountMatchesList(self::DONORS, ['donorType' => 'subscriber'], 0);
        $this->assertCountMatchesList(self::DONORS, ['search' => 'Zed', 'donorType' => 'one-time'], 2);
    }

    /**
     * @since TBD
     */
    public function testSubscriptionsCountMatchesList(): void
    {
        Subscription::factory()->count(5)->create();

        $this->assertCountMatchesList(self::SUBSCRIPTIONS, ['testMode' => 'true'], 5);
        $this->assertCountMatchesList(self::SUBSCRIPTIONS, ['testMode' => 'true', 'status' => 'pending'], 5);
        $this->assertCountMatchesList(self::SUBSCRIPTIONS, ['testMode' => 'true', 'status' => 'active'], 0);
        $this->assertCountMatchesList(self::SUBSCRIPTIONS, ['testMode' => 'true', 'search' => 'nobody-matches-this'], 0);
    }

    /**
     * @since TBD
     */
    public function testFormsCountMatchesList(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createSimpleDonationForm(['form' => ['post_title' => $i < 2 ? 'Zed form ' . $i : 'Other form ' . $i]]);
        }

        $this->assertCountMatchesList(self::FORMS, ['status' => 'any'], 5);
        $this->assertCountMatchesList(self::FORMS, ['status' => 'any', 'search' => 'Zed'], 2);
        $this->assertCountMatchesList(self::FORMS, ['status' => 'any', 'search' => 'Nobody'], 0);
        $this->assertCountMatchesList(self::FORMS, ['status' => 'trash'], 0);
    }

    /**
     * @since TBD
     */
    public function testCampaignsCountMatchesList(): void
    {
        foreach (['Zed one', 'Zed two', 'Amy', 'Bob', 'Cat'] as $title) {
            Campaign::factory()->create(['title' => $title]);
        }

        $this->assertCountMatchesList(self::CAMPAIGNS, [], 5);
        $this->assertCountMatchesList(self::CAMPAIGNS, ['search' => 'Zed'], 2);
        $this->assertCountMatchesList(self::CAMPAIGNS, ['search' => 'Nobody'], 0);
    }

    /**
     * @since TBD
     */
    public function testEventsCountMatchesList(): void
    {
        foreach (['Zed one', 'Zed two', 'Amy', 'Bob', 'Cat'] as $title) {
            Event::factory()->create(['title' => $title]);
        }

        $this->assertCountMatchesList(self::EVENTS, [], 5);
        $this->assertCountMatchesList(self::EVENTS, ['search' => 'Zed'], 2);
        $this->assertCountMatchesList(self::EVENTS, ['search' => 'Nobody'], 0);
    }

    /**
     * @since TBD
     */
    public function testWithCountDefaultsToTrueAndIsOptional(): void
    {
        Donor::factory()->count(3)->create();

        $explicit = $this->get(self::DONORS, ['withCount' => 'true'])->get_data();
        $default = $this->get(self::DONORS)->get_data();

        $this->assertSame($default, $explicit);
        $this->assertSame(3, $default['totalItems']);
        $this->assertArrayNotHasKey('hasNextPage', $default);
    }

    /**
     * @dataProvider countRoutes
     *
     * @since TBD
     */
    public function testCountRouteChecksPermissions(string $route): void
    {
        wp_set_current_user(0);

        $this->assertSame(401, $this->get($route . '/count')->get_status());

        wp_set_current_user($this->factory()->user->create(['role' => 'subscriber']));

        $this->assertSame(403, $this->get($route . '/count')->get_status());
    }

    /**
     * @since TBD
     *
     * @return array<string, array{0: string}>
     */
    public function countRoutes(): array
    {
        return [
            'donations' => [self::DONATIONS],
            'donors' => [self::DONORS],
            'subscriptions' => [self::SUBSCRIPTIONS],
            'forms' => [self::FORMS],
            'campaigns' => [self::CAMPAIGNS],
            'events' => [self::EVENTS],
        ];
    }
}
