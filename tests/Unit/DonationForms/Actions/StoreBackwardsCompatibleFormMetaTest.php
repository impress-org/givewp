<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class StoreBackwardsCompatibleFormMetaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testDonationLevelMetaIsStoredOnInsert()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $this->assertEquals('multi', $this->getSingleFormMeta($donationForm->id, '_give_price_option' ));
        $this->assertIsArray($this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS));
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    public function testDonationGoalMetaIsStoredOnInsert()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->make();
        $donationForm->settings->enableDonationGoal = true;
        $donationForm->settings->goalType = GoalType::AMOUNT();
        $donationForm->settings->goalAmount = 500;
        $donationForm->save(); // Trigger created hook.

        $this->assertEquals('enabled', $this->getSingleFormMeta($donationForm->id, DonationFormMetaKeys::GOAL_OPTION));
        $this->assertEquals('amount', $this->getSingleFormMeta($donationForm->id, '_give_goal_format'));
        $this->assertSame('500.000000', $this->getSingleFormMeta($donationForm->id, '_give_set_goal'));
    }

    /**
     * @since 3.0.0
     */
    public function testRecurringMetaIsStoredOnUpdate()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $amountFieldModified = BlockModel::make([
            'clientId' => '8371d4c7-0e8d-4aff-a1a1-b4520f008132',
            'name' => 'givewp/section',
            'isValid' => true,
            'attributes' => [
                'title' => 'How much would you like to donate today?',
                'description' => 'All donations directly impact our organization and help us further our mission.',
            ],
            'innerBlocks' => [
                [
                    'clientId' => 'bddaa0ea-29bf-4143-b62d-aae3396e9b0f',
                    'name' => 'givewp/donation-amount',
                    'isValid' => true,
                    'attributes' => [
                        'label' => 'Donation Amount',
                        'levels' => [
                            ['value' => '10'],
                            ['value' => '25'],
                            ['value' => '50'],
                            ['value' => '100'],
                            ['value' => '250'],
                            ['value' => '500'],
                        ],
                        'priceOption' => 'multi',
                        'setPrice' => '25',
                        'customAmount' => 'true',
                        'customAmountMin' => 1,
                        'recurringBillingPeriodOptions' => ['month'],
                        'recurringEnableOneTimeDonations' => false,
                        'recurringBillingInterval' => 1,
                        'recurringEnabled' => true,
                        'recurringLengthOfTime' => '0',
                        'recurringOptInDefaultBillingPeriod' => 'month',
                    ],
                ],
            ],
        ]);

        $blocks = $donationForm->blocks->getBlocks();
        $blocks[0] = $amountFieldModified;

        $donationForm->blocks = BlockCollection::make($blocks);

        $donationForm->save();

        $this->assertEquals('month', $this->getSingleFormMeta($donationForm->id, '_give_period'));
        $this->assertEquals(1, $this->getSingleFormMeta($donationForm->id, '_give_period_interval'));
        $this->assertEquals(0, $this->getSingleFormMeta($donationForm->id, '_give_times'));
        $this->assertEquals('yes_admin', $this->getSingleFormMeta($donationForm->id, '_give_recurring'));
    }

    /**
     * @since 3.0.0
     */
    public function testRecurringMetaIsStoredOnInsert()
    {
        $amountFieldModified = BlockModel::make([
            'clientId' => '8371d4c7-0e8d-4aff-a1a1-b4520f008132',
            'name' => 'givewp/section',
            'isValid' => true,
            'attributes' => [
                'title' => 'How much would you like to donate today?',
                'description' => 'All donations directly impact our organization and help us further our mission.',
            ],
            'innerBlocks' => [
                [
                    'clientId' => 'bddaa0ea-29bf-4143-b62d-aae3396e9b0f',
                    'name' => 'givewp/donation-amount',
                    'isValid' => true,
                    'attributes' => [
                        'label' => 'Donation Amount',
                        'levels' => [
                            ['value' => '10'],
                            ['value' => '25'],
                            ['value' => '50'],
                            ['value' => '100'],
                            ['value' => '250'],
                            ['value' => '500'],
                        ],
                        'priceOption' => 'multi',
                        'setPrice' => '25',
                        'customAmount' => 'true',
                        'customAmountMin' => 1,
                        'recurringBillingPeriodOptions' => ['month'],
                        'recurringEnableOneTimeDonations' => false,
                        'recurringBillingInterval' => 1,
                        'recurringEnabled' => true,
                        'recurringLengthOfTime' => '0',
                        'recurringOptInDefaultBillingPeriod' => 'month',
                    ],
                ],
            ],
        ]);

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'blocks' => BlockCollection::make([$amountFieldModified])
        ]);

        $this->assertEquals('month', $this->getSingleFormMeta($donationForm->id, '_give_period'));
        $this->assertEquals(1, $this->getSingleFormMeta($donationForm->id, '_give_period_interval'));
        $this->assertEquals(0, $this->getSingleFormMeta($donationForm->id, '_give_times'));
        $this->assertEquals('yes_admin', $this->getSingleFormMeta($donationForm->id, '_give_recurring'));
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
