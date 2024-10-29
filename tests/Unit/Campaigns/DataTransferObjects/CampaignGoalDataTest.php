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
        $campaign = Campaign::factory()->create(['goal' => 0]);

        $form = DonationForm::factory()->create();
        DB::table('give_campaign_forms')
            ->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        Donation::factory()->create(['formId' => $form->id]);

        $goalData = new CampaignGoalData($campaign);

        $this->assertEquals(0.00, $goalData->percentage);
    }
}
