<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\CreateParentCampaignForDonationForm;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class CreateParentCampaignForDonationFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testCreatesCampaignWithMatchingTitle()
    {
        $form = DonationForm::factory()->create();

        $campaign = (new CreateParentCampaignForDonationForm)($form);

        $this->assertEquals($form->title, $campaign->title);
    }
}
