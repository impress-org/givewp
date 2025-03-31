<?php

namespace Give\Tests\Unit\Campaigns\DataTransferObjects;

use Give\Campaigns\DataTransferObjects\CampaignGoalData;
use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class CampaignGoalDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testGetPercentageDoesNotDivideByZero()
    {
        $goalData = new CampaignGoalData(
            Campaign::factory()->create([
                'goal' => 0
            ])
        );

        $this->assertEquals(0.00, $goalData->percentage);
    }
}
