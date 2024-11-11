<?php

namespace Give\Tests\Unit\Campaigns\DataTransferObjects;

use Give\Campaigns\DataTransferObjects\CampaignGoalData;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;

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
