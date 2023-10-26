<?php

namespace Give\Tests\Unit\Actions;

use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class GenerateDefaultDonationFormBlockCollectionTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testShouldReturnDefaultBlockCollection(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $this->assertCount(3, $blockCollection->getBlocks());
    }

    /**
     * @unreleased
     */
    public function testShouldIncludeDefaultDonationAmountBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/donation-amount');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals(
            $block->getAttributes(),
            [
                "label" => __("Donation Amount", 'give'),
                "levels" => [
                    10,
                    25,
                    50,
                    100,
                    250,
                    500
                ],
                "defaultLevel" => 10,
                "priceOption" => "multi",
                "setPrice" => 25,
                "customAmount" => true,
                "customAmountMin" => 1,
                "recurringBillingPeriodOptions" => [
                    "month"
                ],
                "recurringBillingInterval" => 1,
                "recurringEnabled" => false,
                "recurringLengthOfTime" => "0",
                "recurringOptInDefaultBillingPeriod" => "month",
                "recurringEnableOneTimeDonations" => true
            ]
        );
    }

    /**
     * @unreleased
     */
    public function testShouldIncludeDefaultDonorNameBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/donor-name');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals(
            $block->getAttributes(),
            [
                "showHonorific" => false,
                "honorifics" => [
                    __("Mr", 'give'),
                    __("Ms", 'give'),
                    __("Mrs", 'give')
                ],
                "firstNameLabel" => __("First name", 'give'),
                "firstNamePlaceholder" => __("First name", 'give'),
                "lastNameLabel" => __("Last name", 'give'),
                "lastNamePlaceholder" => __("Last name", 'give'),
                "requireLastName" => false
            ]
        );
    }

    /**
     * @unreleased
     */
    public function testShouldIncludeDefaultEmailBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/email');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals(
            $block->getAttributes(),
            [
                "label" => __("Email Address", 'give'),
                "isRequired" => true,
            ]
        );
    }

    /**
     * @unreleased
     */
    public function testShouldIncludeDefaultDonationSummaryBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/donation-summary');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals([], $block->getAttributes());
    }

    /**
     * @unreleased
     */
    public function testShouldIncludeDefaultPaymentGatewaysBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/payment-gateways');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals([], $block->getAttributes());
    }
}