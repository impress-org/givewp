<?php

namespace Give\Tests\Unit\Campaigns\DataTransferObjects;

use Give\Campaigns\DataTransferObjects\CampaignGoalData;
use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
final class CampaignGoalDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
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
