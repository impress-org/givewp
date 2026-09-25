<?php

namespace Give\Tests\Unit\Campaigns;

use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class CampaignDefaultFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A Campaign with defaultFormId = 0 must return null from defaultForm()
     * instead of passing 0 to DonationFormsRepository::getById() and fataling.
     *
     * @unreleased
     */
    public function testDefaultFormReturnsNullWhenDefaultFormIdIsZero()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'defaultFormId' => 0,
        ]);

        $this->assertNull($campaign->defaultForm());
    }
}
