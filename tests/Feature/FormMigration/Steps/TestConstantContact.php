<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\ConstantContact;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @since 3.7.0
 *
 * @covers \Give\FormMigration\Steps\DonationGoal
 */
class TestConstantContact extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateConstantContactBlockAttributesWithV2FormMeta(): void
    {
        $meta = [
            '_give_constant_contact_custom_label'    => 'Subscribe to our newsletter?',
            '_give_constant_contact_checked_default' => 'on',
            '_give_constant_contact'                 => ['1928414891'],
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $constantContact = new ConstantContact($payload);

        $constantContact->process();

        $block = $payload->formV3->blocks->findByName('givewp/constantcontact');

        $this->assertTrue(true, $block->getAttribute('checked' === 'on'));
        $this->assertSame($meta['_give_constant_contact_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_constant_contact'], $block->getAttribute('selectedEmailLists'));
    }

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateConstantContactBlockAttributesWithGlobalSettings(): void
    {
        $meta = [
            'give_constant_contact_label'           => 'Subscribe to our newsletter?',
            'give_constant_contact_checked_default' => 'on',
            'give_constant_contact_list'            => ['1928414891'],
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        foreach ($meta as $key => $value) {
            give_update_option($key, $value);
        }

        $constantContact = new ConstantContact($payload);

        $constantContact->process();

        $block = $payload->formV3->blocks->findByName('givewp/constantcontact');

        $this->assertTrue(true, $block->getAttribute('checked' === 'on'));
        $this->assertSame($meta['give_constant_contact_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_constant_contact_list'], $block->getAttribute('selectedEmailLists'));
    }

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateConstantContactBlockAttributesWhenNoMeta(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $constantContact = new ConstantContact($payload);

        $constantContact->process();

        $block = $payload->formV3->blocks->findByName('givewp/constantcontact');

        $this->assertTrue(true, $block->getAttribute('checked' === 'on'));
        $this->assertSame('Subscribe to our newsletter?', $block->getAttribute('label'));
        $this->assertNull(null, $block->getAttribute('selectedEmailLists'));
    }
}
