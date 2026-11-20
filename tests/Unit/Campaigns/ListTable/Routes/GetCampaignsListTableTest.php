<?php

namespace Give\Tests\Unit\Campaigns\ListTable\Routes;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since TBD
 */
class GetCampaignsListTableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
    }

    /**
     * @since TBD
     */
    private function listCampaigns(array $params = []): WP_REST_Response
    {
        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/givewp/v3/campaigns/list-table');
        $request->set_query_params($params);

        return rest_do_request($request);
    }

    /**
     * @since TBD
     */
    private function assertListedIds(array $expected, WP_REST_Response $response): void
    {
        $this->assertSame(200, $response->get_status());

        $ids = array_map('intval', array_column($response->get_data()['items'], 'id'));
        sort($ids);
        sort($expected);

        $this->assertSame($expected, $ids);
        $this->assertSame(count($expected), $response->get_data()['totalItems']);
    }

    /**
     * @since TBD
     */
    private function createWithStatus(string $status, array $args = []): Campaign
    {
        return Campaign::factory()->create(array_merge(['status' => new CampaignStatus($status)], $args));
    }

    /**
     * @since TBD
     */
    public function testStatusesFilterUsesStoredValues()
    {
        $active = $this->createWithStatus('active');
        $inactive = $this->createWithStatus('inactive');
        $archived = $this->createWithStatus('archived');

        $this->assertListedIds([$inactive->id], $this->listCampaigns(['statuses' => 'inactive']));
        $this->assertListedIds([$archived->id], $this->listCampaigns(['statuses' => 'archived']));
        $this->assertListedIds([$active->id], $this->listCampaigns(['statuses' => ['active']]));
    }

    /**
     * @since TBD
     */
    public function testStatusesAcceptsSeveralValues()
    {
        $active = $this->createWithStatus('active');
        $inactive = $this->createWithStatus('inactive');
        $this->createWithStatus('archived');
        $draft = $this->createWithStatus('draft');

        $this->assertListedIds(
            [$active->id, $inactive->id, $draft->id],
            $this->listCampaigns(['statuses' => ['active', 'inactive', 'draft']])
        );
    }

    /**
     * @since TBD
     */
    public function testLegacyStatusInactiveStillMeansArchived()
    {
        $this->createWithStatus('active');
        $this->createWithStatus('inactive');
        $archived = $this->createWithStatus('archived');

        $this->assertListedIds([$archived->id], $this->listCampaigns(['status' => 'inactive']));
    }

    /**
     * @since TBD
     */
    public function testLegacyStatusIsIgnoredWhenStatusesHasValues()
    {
        $this->createWithStatus('active');
        $inactive = $this->createWithStatus('inactive');

        $this->assertListedIds([$inactive->id], $this->listCampaigns(['status' => 'any', 'statuses' => 'inactive']));
    }

    /**
     * @since TBD
     */
    public function testAnyStillHidesArchived()
    {
        $active = $this->createWithStatus('active');
        $inactive = $this->createWithStatus('inactive');
        $this->createWithStatus('archived');

        $this->assertListedIds([$active->id, $inactive->id], $this->listCampaigns(['status' => 'any']));
    }

    /**
     * @since TBD
     */
    public function testFiltersByCreatedDateRange()
    {
        $before = Campaign::factory()->create(['createdAt' => new DateTime('2026-08-31 23:59:59', wp_timezone())]);
        $first = Campaign::factory()->create(['createdAt' => new DateTime('2026-09-01 00:00:00', wp_timezone())]);
        $last = Campaign::factory()->create(['createdAt' => new DateTime('2026-09-15 23:30:00', wp_timezone())]);
        $after = Campaign::factory()->create(['createdAt' => new DateTime('2026-09-16 00:00:00', wp_timezone())]);

        $this->assertListedIds(
            [$first->id, $last->id],
            $this->listCampaigns(['start' => '2026-09-01T00:00:00', 'end' => '2026-09-15T23:59:59'])
        );
        $this->assertListedIds(
            [$last->id, $after->id],
            $this->listCampaigns(['start' => '2026-09-02T00:00:00'])
        );
        $this->assertListedIds(
            [$before->id],
            $this->listCampaigns(['end' => '2026-08-31T23:59:59'])
        );
    }

    /**
     * @since TBD
     */
    public function testSearchRespectsStatusFilter()
    {
        $byTitle = $this->createWithStatus('active', ['title' => 'Spring drive']);
        $byDescription = $this->createWithStatus('active', ['title' => 'Other', 'shortDescription' => 'A spring push']);
        $this->createWithStatus('archived', ['title' => 'Spring archive']);
        $this->createWithStatus('archived', ['title' => 'Summer', 'shortDescription' => 'Not spring again']);

        $this->assertListedIds(
            [$byTitle->id, $byDescription->id],
            $this->listCampaigns(['search' => 'spring', 'status' => 'any'])
        );
    }

    /**
     * @since TBD
     */
    public function testTotalItemsMatchesRows()
    {
        $this->createWithStatus('active');
        $this->createWithStatus('inactive');
        $this->createWithStatus('archived');

        $data = $this->listCampaigns(['statuses' => ['active', 'inactive'], 'perPage' => 1])->get_data();

        $this->assertCount(1, $data['items']);
        $this->assertSame(2, $data['totalItems']);
        $this->assertSame(2, $data['totalPages']);
    }

    /**
     * @since TBD
     */
    public function testFiftyStatusesAreAccepted()
    {
        $this->assertSame(200, $this->listCampaigns(['statuses' => array_fill(0, 50, 'active')])->get_status());
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidParamsProvider
     */
    public function testRejectsInvalidValues(array $params)
    {
        $response = $this->listCampaigns($params);

        $this->assertSame(400, $response->get_status());
        $this->assertSame('rest_invalid_param', $response->get_data()['code']);
    }

    /**
     * @since TBD
     */
    public function invalidParamsProvider(): array
    {
        return [
            'fifty-one statuses' => [['statuses' => array_fill(0, 51, 'active')]],
            'unknown status' => [['statuses' => ['nope']]],
            'one unknown status in a list' => [['statuses' => ['active', 'nope']]],
            'bad start' => [['start' => 'not a date']],
        ];
    }
}
