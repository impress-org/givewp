<?php

namespace Give\Tests\Unit\Actions;

use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @since 3.1.0
 */
class GenerateDefaultDonationFormBlockCollectionTest extends TestCase
{
    /**
     * @since 3.1.0
     */
    public function testShouldReturnDefaultBlockCollection(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $this->assertCount(3, $blockCollection->getBlocks());
    }

    /**
     * @since 3.1.0
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
                    ['value' => 10, 'checked' => true],
                    ['value' => 25],
                    ['value' => 50],
                    ['value' => 100],
                    ['value' => 250],
                    ['value' => 500],
                ],
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
     * @since 3.1.0
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
     * @since 3.1.0
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
     * @since 3.1.0
     */
    public function testShouldIncludeDefaultDonationSummaryBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/donation-summary');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals([], $block->getAttributes());
    }

    /**
     * @since 3.1.0
     */
    public function testShouldIncludeDefaultPaymentGatewaysBlock(): void
    {
        $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();

        $block = $blockCollection->findByName('givewp/payment-gateways');

        $this->assertInstanceOf(BlockModel::class, $block);

        $this->assertEquals([], $block->getAttributes());
    }
}
