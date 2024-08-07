<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\DoubleTheDonation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.8.0
 *
 * @covers \Give\FormMigration\Steps\DoubleTheDonation
 */
class TestDoubleTheDonation extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    public function testProcessShouldUpdateDoubleTheDonationBlockAttributes(): void
    {
        $meta = [
            'give_dtd_label' => 'DTD Label',
        ];

        $company = [
            'company_id'   => '',
            'company_name' => '',
            'entered_text' => '',
        ];

        $formV2  = $this->createSimpleDonationForm(['meta' => $meta]);
        $payload = FormMigrationPayload::fromFormV2($formV2);

        $dtd = new DoubleTheDonation($payload);
        $dtd->process();

        $block = $payload->formV3->blocks->findByName('givewp/dtd');

        $this->assertSame($meta['give_dtd_label'], $block->getAttribute('label'));
        $this->assertEqualsIgnoringCase($company, $block->getAttribute('company'));
    }
}
