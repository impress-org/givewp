<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\ActiveCampaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestActiveCampaign extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.10.0
     */
    public function testProcessShouldUpdateActiveCampaignBlockAttributesFromV2FormMeta(): void
    {
        $meta = [
            'give_activecampaign_label'            => __('Subscribe to our newsletter?'),
            'give_activecampaign_lists'            => ['1', '2'],
            'give_activecampaign_tags'             => ['tag 1', 'tag 2'],
            'give_activecampaign_checkbox_default' => true,
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new ActiveCampaign($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('give-activecampaign/activecampaign');

        $this->assertSame($meta['give_activecampaign_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_activecampaign_lists'], $block->getAttribute('selectedLists'));
        $this->assertSame($meta['give_activecampaign_tags'], $block->getAttribute('selectedTags'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @since 3.10.0
     */
    public function testProcessShouldUpdateActiveCampaignBlockAttributesFromGlobalSettings(): void
    {
        $meta = [
            'give_activecampaign_label'            => __('Subscribe to our newsletter?'),
            'give_activecampaign_lists'            => ['1', '2'],
            'give_activecampaign_tags'             => ['tag 1', 'tag 2'],
            'give_activecampaign_checkbox_default' => true,
        ];

        foreach ($meta as $key => $value) {
            give_update_option($key, $value);
        }

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new ActiveCampaign($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('give-activecampaign/activecampaign');

        $this->assertSame($meta['give_activecampaign_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_activecampaign_lists'], $block->getAttribute('selectedLists'));
        $this->assertSame($meta['give_activecampaign_tags'], $block->getAttribute('selectedTags'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @since 3.10.0
     */
    public function testProcessShouldUpdateActiveCampaignBlockAttributesWhenNoMeta(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new ActiveCampaign($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('give-activecampaign/activecampaign');

        $this->assertSame(__('Subscribe to our newsletter?'), $block->getAttribute('label'));
        $this->assertSame([], $block->getAttribute('selectedLists'));
        $this->assertNull(null, $block->getAttribute('selectedTags'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }
}
