<?php

namespace Give\Tests\Unit\DonationForms\Endpoints;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Endpoints\ListDonationForms;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Helpers\Language;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonationForms extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    private $donationForms = [];

    public function setUp(): void
    {
        parent::setUp();

        $formLevelTypes = ['multi', 'simple'];
        for ( $count = 1; $count <= 5; $count++ ) {
            $formLevelType = $formLevelTypes[round(rand(0, 1))];
            $this->donationForms[] = $formLevelType === 'multi' ? $this->createMultiLevelDonationForm() : $this->createSimpleDonationForm();
        }
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('status', 'any');

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSameSize($this->donationForms, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('status', 'any');

        $expectedItems = $this->getMockColumns($this->donationForms, $sortDirection);

        $listDonationForms = new ListDonationForms();
        $response = $listDonationForms->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return WP_REST_Request
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/forms'
        );
    }

    /**
     * @since 4.19.0
     */
    public function testCampaignNoneFilterReturnsOnlyStandaloneForms()
    {
        $campaign = Campaign::factory()->create(['title' => 'Linked Campaign']);
        $linkedForm = $this->donationForms[0];
        give(CampaignRepository::class)->addCampaignForm($campaign, $linkedForm->id);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('status', 'any');
        $mockRequest->set_param('campaign', 'none');

        $response = (new ListDonationForms())->handleRequest($mockRequest);
        $ids = array_column($response->data['items'], 'id');

        $this->assertCount(count($this->donationForms) - 1, $ids);
        $this->assertNotContains($linkedForm->id, $ids);

        $standaloneIds = array_diff(array_column($this->donationForms, 'id'), [$linkedForm->id]);

        $mockRequest->set_param('campaign', (string)$campaign->id);
        $response = (new ListDonationForms())->handleRequest($mockRequest);
        $this->assertContains($linkedForm->id, array_column($response->data['items'], 'id'));
        $this->assertContains($campaign->defaultFormId, array_column($response->data['items'], 'id'));
        $this->assertEmpty(array_intersect($standaloneIds, array_column($response->data['items'], 'id')));

        $mockRequest->set_param('campaign', '');
        $response = (new ListDonationForms())->handleRequest($mockRequest);

        foreach ($response->data['items'] as $item) {
            if ($item['id'] === $linkedForm->id) {
                $this->assertSame($campaign->id, $item['campaignId']);
                $this->assertStringContainsString('Linked Campaign', $item['formCampaign']);
            } elseif (in_array($item['id'], $standaloneIds, true)) {
                $this->assertSame(0, $item['campaignId']);
                $this->assertSame('No campaign', $item['formCampaign']);
            }
        }
    }

    /**
     * @since 4.19.0
     */
    public function testFlagsDefaultCampaignFormWhenRequestNamesNoCampaign()
    {
        $campaign = Campaign::factory()->create();
        $attachedForm = $this->donationForms[0];
        give(CampaignRepository::class)->addCampaignForm($campaign, $attachedForm->id);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('status', 'any');

        $response = (new ListDonationForms())->handleRequest($mockRequest);
        $flags = array_column($response->data['items'], 'isDefaultCampaignForm', 'id');

        $this->assertTrue($flags[$campaign->defaultFormId]);
        $this->assertFalse($flags[$attachedForm->id]);
    }

    /**
     * @since TBD
     */
    private function listForms(array $params): \WP_REST_Response
    {
        $request = $this->getMockRequest();
        $request->set_param('page', 1);
        $request->set_param('perPage', 30);
        $request->set_param('locale', 'en-US');
        $request->set_param('status', 'any');

        foreach ($params as $key => $value) {
            $request->set_param($key, $value);
        }

        return (new ListDonationForms())->handleRequest($request);
    }

    /**
     * @since TBD
     */
    private function setFormStatus(int $id, string $status): void
    {
        wp_update_post(['ID' => $id, 'post_status' => $status]);
    }

    /**
     * @since TBD
     */
    private function setFormDate(int $id, string $date): void
    {
        wp_update_post(['ID' => $id, 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date($date)]);
    }

    /**
     * @since TBD
     */
    private function assertListedIds(array $expected, \WP_REST_Response $response): void
    {
        $ids = array_map('intval', array_column($response->data['items'], 'id'));
        sort($ids);
        sort($expected);

        $this->assertSame($expected, $ids);
        $this->assertSame(count($expected), $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiltersByTwoStatuses()
    {
        $this->setFormStatus($this->donationForms[0]->id, 'draft');
        $this->setFormStatus($this->donationForms[1]->id, 'pending');
        $this->setFormStatus($this->donationForms[2]->id, 'trash');

        $response = $this->listForms(['status' => ['draft', 'pending']]);

        $this->assertListedIds([$this->donationForms[0]->id, $this->donationForms[1]->id], $response);
    }

    /**
     * @since TBD
     */
    public function testSingleStatusKeepsItsResult()
    {
        $this->setFormStatus($this->donationForms[0]->id, 'draft');
        $this->setFormStatus($this->donationForms[1]->id, 'trash');

        $this->assertListedIds([$this->donationForms[0]->id], $this->listForms(['status' => 'draft']));
        $this->assertListedIds([$this->donationForms[1]->id], $this->listForms(['status' => ['trash']]));
    }

    /**
     * @since TBD
     */
    public function testAnyStatusKeepsItsResult()
    {
        $this->setFormStatus($this->donationForms[0]->id, 'trash');

        $expected = array_slice(array_column($this->donationForms, 'id'), 1);

        $this->assertListedIds($expected, $this->listForms(['status' => 'any']));
        $this->assertListedIds($expected, $this->listForms(['status' => ['any']]));
    }

    /**
     * @since TBD
     */
    public function testFiltersByTwoCampaigns()
    {
        $first = Campaign::factory()->create();
        $second = Campaign::factory()->create();
        $third = Campaign::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($first, $this->donationForms[0]->id);
        give(CampaignRepository::class)->addCampaignForm($second, $this->donationForms[1]->id);
        give(CampaignRepository::class)->addCampaignForm($third, $this->donationForms[2]->id);

        $response = $this->listForms(['campaign' => $first->id . ',' . $second->id]);

        $this->assertListedIds([
            $this->donationForms[0]->id,
            $this->donationForms[1]->id,
            $first->defaultFormId,
            $second->defaultFormId,
        ], $response);
    }

    /**
     * @since TBD
     */
    public function testFiltersByCreatedDateRange()
    {
        $this->setFormDate($this->donationForms[0]->id, '2026-08-31 23:59:59');
        $this->setFormDate($this->donationForms[1]->id, '2026-09-01 00:00:00');
        $this->setFormDate($this->donationForms[2]->id, '2026-09-15 23:30:00');
        $this->setFormDate($this->donationForms[3]->id, '2026-09-16 00:00:00');
        $this->setFormDate($this->donationForms[4]->id, '2026-09-10 12:00:00');

        $response = $this->listForms(['start' => '2026-09-01T00:00:00', 'end' => '2026-09-15T23:59:59']);

        $this->assertListedIds(
            [$this->donationForms[1]->id, $this->donationForms[2]->id, $this->donationForms[4]->id],
            $response
        );
    }

    /**
     * @since TBD
     */
    public function testFiltersByStartDateOnly()
    {
        $this->setFormDate($this->donationForms[0]->id, '2026-08-31 23:59:59');
        $this->setFormDate($this->donationForms[1]->id, '2026-09-01 00:00:00');
        $this->setFormDate($this->donationForms[2]->id, '2026-09-15 23:30:00');
        $this->setFormDate($this->donationForms[3]->id, '2026-08-01 00:00:00');
        $this->setFormDate($this->donationForms[4]->id, '2026-07-01 00:00:00');

        $response = $this->listForms(['start' => '2026-09-01T00:00:00']);

        $this->assertListedIds([$this->donationForms[1]->id, $this->donationForms[2]->id], $response);
    }

    /**
     * @since TBD
     *
     * @dataProvider listLimitProvider
     */
    public function testListArgsAcceptFiftyAndRejectFiftyOne(string $param, $fifty, $fiftyOne)
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/forms');
        $request->set_query_params([$param => $fifty]);
        $this->assertSame(200, rest_do_request($request)->get_status());

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/forms');
        $request->set_query_params([$param => $fiftyOne]);
        $response = rest_do_request($request);

        $this->assertSame(400, $response->get_status());
        $this->assertSame('rest_invalid_param', $response->get_data()['code']);
    }

    /**
     * @since TBD
     */
    public function listLimitProvider(): array
    {
        return [
            'statuses' => ['status', array_fill(0, 50, 'draft'), array_fill(0, 51, 'draft')],
            'campaigns' => ['campaign', implode(',', range(1001, 1050)), implode(',', range(1001, 1051))],
        ];
    }

    /**
     * @since TBD
     */
    public function testRejectsAnUnknownStatus()
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/forms');
        $request->set_query_params(['status' => 'nope']);
        $response = rest_do_request($request);

        $this->assertSame(400, $response->get_status());
        $this->assertSame('rest_invalid_param', $response->get_data()['code']);
    }

    /**
     * @since 4.0.0 Add support to isDefaultCampaignForm key
     * @since 2.25.0
     *
     * @param DonationForm[]  $donationForms
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $donationForms, string $sortDirection = 'desc'): array
    {
        $formsData = DonationFormDataRepository::forms($donationForms);

        $columns = (new DonationFormsListTable())
            ->setData($formsData)
            ->getColumns();

        $expectedItems = [];
        foreach ( $donationForms as $donationForm ) {
            $expectedItem = [];
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($donationForm, 'en-US');
            }
            $expectedItem['name'] = $donationForm->title;
            $expectedItem['edit'] = add_query_arg(['locale' => Language::getLocale()],
                get_edit_post_link($donationForm->id, 'edit'));
            $expectedItem['permalink'] = get_permalink($donationForm->id);

            $expectedItem['v3form'] = false;
            $expectedItem['status_raw'] = $donationForm->status->getValue();

            $expectedItem['isDefaultCampaignForm'] = false;
            $expectedItem['campaignId'] = 0;
            $expectedItem['campaignType'] = '';
            $expectedItem['campaignTitle'] = '';

            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}

