<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\ConvertKit;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestConvertKit extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.11.0
     */
    public function testProcessShouldUpdateConvertkitBlockAttributesFromV2FormMeta(): void
    {
        $meta = [
            '_give_convertkit_custom_label'    => __('Subscribe to newsletter?' , 'give'),
            '_give_convertkit'                 => '6352843',
            '_give_convertkit_tags'            => ['4619079', '4619080'],
            '_give_convertkit_checked_default' => true,
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $convertkit = new ConvertKit($payload);

        $convertkit->process();

        $block = $payload->formV3->blocks->findByName('givewp-convertkit/convertkit');

        $this->assertSame($meta['_give_convertkit_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_convertkit_tags'], $block->getAttribute('tagSubscribers'));
        $this->assertSame($meta['_give_convertkit'], $block->getAttribute('selectedForm'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @since 3.11.0
     */
    public function testProcessShouldUpdateConvertkitBlockAttributesFromGlobalSettings(): void
    {
        $meta = [
            'give_convertkit_label'           => __('Subscribe to newsletter?', 'give'),
            'give_convertkit_list'            => '6352843',
            '_give_convertkit_tags'           => ['4619079', '4619080'],
            'give_convertkit_checked_default' => true,
        ];

        foreach ($meta as $key => $value) {
            give_update_option($key, $value);
        }

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $convertkit = new ConvertKit($payload);

        $convertkit->process();

        $block = $payload->formV3->blocks->findByName('givewp-convertkit/convertkit');

        $this->assertSame($meta['give_convertkit_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_convertkit_list'], $block->getAttribute('selectedForm'));
        $this->assertSame($meta['_give_convertkit_tags'], $block->getAttribute('tagSubscribers'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @since 3.11.0
     */
    public function testProcessShouldUpdateConvertkitBlockAttributesWhenNoMeta(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $convertkit = new ConvertKit($payload);

        $convertkit->process();

        $block = $payload->formV3->blocks->findByName('givewp-convertkit/convertkit');

        $this->assertSame(__('Subscribe to newsletter?', 'give'), $block->getAttribute('label'));
        $this->assertSame('', $block->getAttribute('selectedForm'));
        $this->assertNull(null, $block->getAttribute('tagSubscribers'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }
}
