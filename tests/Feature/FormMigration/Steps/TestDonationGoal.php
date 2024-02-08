<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\DonationGoal;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @since 3.4.0
 *
 * @covers \Give\FormMigration\Steps\DonationGoal
 */
class TestDonationGoal extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.4.0
     */
    public function testProcessShouldUpdateDonationFormDonationGoalSettings(): void
    {
        $meta = [
            '_give_goal_option' => 'enabled',
            '_give_goal_setting' => 'enabled',
            '_give_goal_format' => 'amount',
            '_give_set_goal' => 5000,
            '_give_close_form_when_goal_achieved' => 'enabled',
            '_give_form_goal_achieved_message' => __( 'Thank you to all our donors, we have met our fundraising goal.', 'give' ),
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $donationGoal = new DonationGoal($payload);

        $donationGoal->process();

        $settings = $payload->formV3->settings;

        $this->assertTrue(true, $settings->enableDonationGoal);
        $this->assertTrue($settings->goalType->isAmount());
        $this->assertSame((string)$meta['_give_set_goal'], $settings->goalAmount);
        $this->assertTrue(true, $settings->enableAutoClose);
        $this->assertSame($meta['_give_form_goal_achieved_message'], $settings->goalAchievedMessage);
    }
}
