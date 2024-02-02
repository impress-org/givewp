<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\DonationOptions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestDonationOptions extends TestCase {
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testProcessShouldUpdateDonationAmountBlockAttributes(): void
    {
        $meta = [
            '_give_price_option' => 'set',
            '_give_set_price' => '100',
            '_give_custom_amount' => 'enabled',
            '_give_custom_amount_range_minimum' => '1',
            '_give_custom_amount_range_maximum' => '1000',
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $donationOptions = new DonationOptions($payload);

        $donationOptions->process();

        $block = $payload->formV3->blocks->findByName('givewp/donation-amount');

        $this->assertSame($meta['_give_price_option'], $block->getAttribute('priceOption'));
        $this->assertSame($meta['_give_set_price'], $block->getAttribute('setPrice'));
        $this->assertTrue($block->getAttribute('customAmount'));
        $this->assertSame((float)$meta['_give_custom_amount_range_minimum'], $block->getAttribute('customAmountMin'));
        $this->assertSame((float)$meta['_give_custom_amount_range_maximum'], $block->getAttribute('customAmountMax'));
    }

    /**
     * @unreleased
     */
    public function testProcessShouldUpdateDonationAmountBlockAttributesWithDonationLevels(): void
    {
        $meta = [
            '_give_custom_amount' => 'enabled',
            '_give_custom_amount_range_minimum' => '1',
            '_give_custom_amount_range_maximum' => '1000',
        ];

        $formV2 = $this->createMultiLevelDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $donationOptions = new DonationOptions($payload);

        $donationOptions->process();

        $block = $payload->formV3->blocks->findByName('givewp/donation-amount');

        $this->assertSame([10.00, 25.00, 50.00, 100.00], $block->getAttribute('levels'));
        $this->assertTrue($block->getAttribute('customAmount'));
        $this->assertSame((float)$meta['_give_custom_amount_range_minimum'], $block->getAttribute('customAmountMin'));
        $this->assertSame((float)$meta['_give_custom_amount_range_maximum'], $block->getAttribute('customAmountMax'));
    }
}
