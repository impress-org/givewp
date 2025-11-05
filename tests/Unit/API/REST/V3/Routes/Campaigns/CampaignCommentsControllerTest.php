<?php

namespace Unit\API\REST\V3\Routes\Campaigns;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @unreleased
 */
final class CampaignCommentsControllerTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testReturnsCommentsIncludingAnonymousByDefault(): void
    {
        $campaign = Campaign::factory()->create();

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'comment' => 'Great work!',
            'anonymous' => false,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(2000, 'USD'),
            'comment' => 'Keep it up!',
            'anonymous' => true,
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
        ]);

        $route = '/' . CampaignRoute::NAMESPACE . '/campaigns/' . $campaign->id . '/comments';
        $request = $this->createRequest('GET', $route);
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        $this->assertCount(2, $data);

        $comments = array_column($data, 'comment');
        $this->assertContains('Great work!', $comments);
        $this->assertContains('Keep it up!', $comments);

        $anonymousFlags = array_column($data, 'anonymous');
        $this->assertContains(true, $anonymousFlags);
        $this->assertContains(false, $anonymousFlags);

        $this->assertIsString($data[0]['date']);
        $this->assertArrayHasKey('donorName', $data[0]);
    }

    /**
     * @unreleased
     */
    public function testExcludesAnonymousWhenAnonymousParamFalse(): void
    {
        $campaign = Campaign::factory()->create();

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'comment' => 'Visible comment',
            'anonymous' => false,
            'firstName' => 'Alice',
            'lastName' => 'Walker',
        ]);

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(2000, 'USD'),
            'comment' => 'Hidden anonymous',
            'anonymous' => true,
            'firstName' => 'Bob',
            'lastName' => 'Marley',
        ]);

        $route = '/' . CampaignRoute::NAMESPACE . '/campaigns/' . $campaign->id . '/comments';
        $request = $this->createRequest('GET', $route);
        $request->set_param('id', $campaign->id);
        $request->set_param('anonymous', false);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        $this->assertCount(1, $data);
        $this->assertEquals('Visible comment', $data[0]['comment']);
        $this->assertFalse($data[0]['anonymous']);
    }
}
