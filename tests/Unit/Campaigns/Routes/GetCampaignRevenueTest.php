<?php

namespace Give\Tests\Unit\Campaigns\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\GetCampaignRevenue;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

class GetCampaignRevenueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     * @throws Exception
     */
    public function testShouldReturnRevenueByDay(): void
    {
        $campaign = Campaign::factory()->create([
            'endDate' => date_create('2025-03-10 00:00:00'),
        ]);

        $dates = [
            '2025-03-01',
            '2025-03-02',
            '2025-03-03',
        ];

        foreach ($dates as $date) {
            Donation::factory()->create([
                'campaignId' => $campaign->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => date_create($date . ' 00:00:00'),
            ]);
        }

        $request = new WP_REST_Request('GET', "/give-api/v2/campaigns/$campaign->id/revenue");
        $request->set_param('id', $campaign->id);

        $route = new GetCampaignRevenue();
        $response = $route->handleRequest($request);

        $this->assertEquals([
            $this->getResultData('2025-03-01', '10'),
            $this->getResultData('2025-03-02', '10'),
            $this->getResultData('2025-03-03', '10'),
            $this->getResultData('2025-03-04'),
            $this->getResultData('2025-03-05'),
            $this->getResultData('2025-03-06'),
            $this->getResultData('2025-03-07'),
            $this->getResultData('2025-03-08'),
            $this->getResultData('2025-03-09'),
            $this->getResultData('2025-03-10'),
        ], $response->data);
    }

    /**
     * @since 4.0.0
     * @throws Exception
     */
    public function testShouldReturnRevenueByMonth(): void
    {
        $campaign = Campaign::factory()->create([
            'endDate' => date_create('2025-03-10 00:00:00'),
        ]);

        $dates = [
            '2024-01-01',
            '2024-01-02',
            '2024-01-03',
            '2024-02-01',
            '2025-03-01',
            '2025-03-02',
            '2025-03-03',
        ];

        foreach ($dates as $date) {
            Donation::factory()->create([
                'campaignId' => $campaign->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => date_create($date . ' 00:00:00'),
            ]);
        }

        $request = new WP_REST_Request('GET', "/give-api/v2/campaigns/$campaign->id/revenue");
        $request->set_param('id', $campaign->id);

        $route = new GetCampaignRevenue();
        $response = $route->handleRequest($request);

        $this->assertEquals([
            $this->getResultData('2024-01', '30'),
            $this->getResultData('2024-02', '10'),
            $this->getResultData('2024-03'),
            $this->getResultData('2024-04'),
            $this->getResultData('2024-05'),
            $this->getResultData('2024-06'),
            $this->getResultData('2024-07'),
            $this->getResultData('2024-08'),
            $this->getResultData('2024-09'),
            $this->getResultData('2024-10'),
            $this->getResultData('2024-11'),
            $this->getResultData('2024-12'),
            $this->getResultData('2025-01'),
            $this->getResultData('2025-02'),
            $this->getResultData('2025-03', '30'),

        ], $response->data);
    }

    /**
     * @since 4.0.0
     * @throws Exception
     */
    public function testShouldReturnRevenueByYear(): void
    {
        $campaign = Campaign::factory()->create([
            'endDate' => date_create('2025-03-10 00:00:00'),
        ]);

        $dates = [
            '2020-01-01',
            '2020-01-02',
            '2021-01-01',
            '2024-01-01',
            '2024-01-02',
            '2024-01-03',
            '2024-02-01',
            '2025-03-01',
            '2025-03-02',
            '2025-03-03',
        ];

        foreach ($dates as $date) {
            Donation::factory()->create([
                'campaignId' => $campaign->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => date_create($date . ' 00:00:00'),
            ]);
        }

        $request = new WP_REST_Request('GET', "/give-api/v2/campaigns/$campaign->id/revenue");
        $request->set_param('id', $campaign->id);

        $route = new GetCampaignRevenue();
        $response = $route->handleRequest($request);

        $this->assertEquals([
            $this->getResultData('2020', '20'),
            $this->getResultData('2021', '10'),
            $this->getResultData('2022'),
            $this->getResultData('2023'),
            $this->getResultData('2024', '40'),
            $this->getResultData('2025', '30'),
        ], $response->data);
    }

    /**
     * @since 4.0.0
     */
    public function getResultData(string $dateCreated, $amount = 0): array
    {
        return [
            'date' => $dateCreated,
            'amount' => $amount ?? 0,
        ];
    }
}
