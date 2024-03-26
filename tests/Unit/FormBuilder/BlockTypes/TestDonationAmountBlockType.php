<?php

namespace Give\Tests\Unit\FormBuilder\BlockTypes;

use Exception;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationAmountBlockType extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws Exception
     */
    public function testGetName(): void
    {
        $blockModel = $this->getDefaultDonationAmountBlockModel();
        $block = new \Give\FormBuilder\BlockTypes\DonationAmountBlockType($blockModel);

        $this->assertSame('givewp/donation-amount', $block::name());
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testDefaultBlockModelAttributesMatchBlockTypeProperties(): void
    {
        $blockModel = $this->getDefaultDonationAmountBlockModel();
        $block = new \Give\FormBuilder\BlockTypes\DonationAmountBlockType($blockModel);

        $this->assertSame(__("Donation Amount", 'give'), $block->label);
        $this->assertSame([
            10,
            25,
            50,
            100,
            250,
            500
        ], $block->levels);
        $this->assertSame(10.00, $block->defaultLevel);
        $this->assertSame("multi", $block->priceOption);
        $this->assertSame(25, $block->setPrice);
        $this->assertTrue($block->customAmount);
        $this->assertSame(1, $block->customAmountMin);
        $this->assertNull($block->customAmountMax);
        $this->assertFalse($block->recurringEnabled);
        $this->assertSame(1, $block->recurringBillingInterval);
        $this->assertSame(["month"], $block->recurringBillingPeriodOptions);
        $this->assertSame(0, $block->recurringLengthOfTime);
        $this->assertTrue($block->recurringEnableOneTimeDonations);
        $this->assertSame('month', $block->recurringOptInDefaultBillingPeriod);
    }

    /**
     * @unreleased
     */
    public function getDefaultDonationAmountBlockModel(): BlockModel
    {
        return (new GenerateDefaultDonationFormBlockCollection())()->findByName('givewp/donation-amount');
    }
}
