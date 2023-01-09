<?php

namespace Give\Tests\Unit\DonationForm\Actions;

use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\ValueObjects\GoalType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class StoreBackwardsCompatibleFormMetaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDonationLevelMetaIsStoredOnInsert()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $this->assertEquals('multi', $this->getSingleFormMeta($donationForm->id, '_give_price_option' ));
        $this->assertIsArray($this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS));
    }

    /**
     * @unreleased
     */
    public function testDonationLevelMetaIsStoredOnUpdate()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        give()->form_meta->delete_meta($donationForm->id, '_give_price_option');
        give()->form_meta->delete_meta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS);

        $donationForm->save(); // Trigger update.

        $this->assertEquals('multi', $this->getSingleFormMeta($donationForm->id, '_give_price_option' ));
        $this->assertIsArray($this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS));
    }

    /**
     * @unreleased
     */
    public function testDonationGoalMetaIsStoredOnInsert()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->make();
        $donationForm->settings->enableDonationGoal = true;
        $donationForm->settings->goalType = GoalType::AMOUNT();
        $donationForm->settings->goalAmount = 500;
        $donationForm->save(); // Trigger created hook.

        $this->assertEquals('enabled', $this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::GOAL_OPTION ));
        $this->assertEquals('amount', $this->getSingleFormMeta($donationForm->id, '_give_goal_format' ));
        $this->assertSame('500.000000', $this->getSingleFormMeta($donationForm->id, '_give_set_goal' ));
    }

    /**
     * @unreleased
     */
    public function testDonationGoalMetaIsStoredOnUpdate()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        give()->form_meta->delete_meta($donationForm->id, DonationFormMetaKeys::GOAL_OPTION);
        give()->form_meta->delete_meta($donationForm->id, '_give_goal_format');
        give()->form_meta->delete_meta($donationForm->id, '_give_set_goal');

        $donationForm->settings->enableDonationGoal = true;
        $donationForm->settings->goalType = GoalType::AMOUNT();
        $donationForm->settings->goalAmount = 500;
        $donationForm->save(); // Trigger update hook.

        $this->assertEquals('enabled', $this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::GOAL_OPTION ));
        $this->assertEquals('amount', $this->getSingleFormMeta($donationForm->id, '_give_goal_format' ));
        $this->assertSame('500.000000', $this->getSingleFormMeta($donationForm->id, '_give_set_goal' ));
    }

    /**
     * @unreleased
     */
    public function testDonationGoalMetaUsesGoalTypeMetaKeyAmount()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->make();
        $donationForm->settings->goalType = GoalType::AMOUNT();
        $donationForm->save();

        $this->assertNotNull($this->getSingleFormMeta($donationForm->id, '_give_set_goal' ));
    }

    /**
     * @unreleased
     */
    public function testDonationGoalMetaUsesGoalTypeMetaKeyDonations()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->make();
        $donationForm->settings->goalType = GoalType::DONATIONS();
        $donationForm->save();

        $this->assertNotNull($this->getSingleFormMeta($donationForm->id, '_give_number_of_donation_goal' ));
    }

    /**
     * @unreleased
     */
    public function testDonationGoalMetaUsesGoalTypeMetaKeyDonors()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->make();
        $donationForm->settings->goalType = GoalType::DONORS();
        $donationForm->save();

        $this->assertNotNull($this->getSingleFormMeta($donationForm->id, '_give_number_of_donor_goal' ));
    }

    protected function getSingleFormMeta($formId, $metaKey)
    {
        return give()->form_meta->get_meta($formId, $metaKey, $single = true);
    }
}
