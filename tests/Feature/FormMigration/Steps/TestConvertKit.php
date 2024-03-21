<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\ConvertKit;
use Give\FormMigration\Steps\Mailchimp;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestConvertKit extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesFromV2FormMeta(): void
    {
        $meta = [
            '_give_convertkit_custom_label'    => __('Subscribe to newsletter?'),
            '_give_convertkit_tags'            => [
                ["id" => 4619079, "name" => "Hiking"],
                ["id" => 4619080, "name" => "Swimming"],
            ],
            '_give_convertkit'                 => [

                ["id" => 6352843, "name" => 'Charlotte form'],
            ],
            '_give_convertkit_checked_default' => true,
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new ConvertKit($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('givewp-convertkit/convertkit');

        $this->assertSame($meta['_give_convertkit_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_convertkit_tags'], $block->getAttribute('subscriberTags'));
        $this->assertSame($meta['_give_convertkit'], $block->getAttribute('defaultAudiences'));
        $this->assertTrue(true, $block->getAttribute('checked'));
    }

    /**
     * @unreleased
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesFromGlobalSettings(): void
    {
        $meta = [
            'give_convertkit_label'           => __('Subscribe to newsletter?'),
            '_give_convertkit_tags'           => [],
            'give_convertkit_list'            => ['de73f3f82f'],
            'give_convertkit_checked_default' => true,
        ];

        foreach ($meta as $key => $value) {
            give_update_option($key, $value);
        }

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new Mailchimp($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('givewp/mailchimp');

        $this->assertSame($meta['give_mailchimp_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_mailchimp_list'], $block->getAttribute('defaultAudiences'));
        $this->assertSame([''], $block->getAttribute('subscriberTags'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('doubleOptIn'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }

    /**
     * @unreleased
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesWhenNoMeta(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new Mailchimp($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('givewp/mailchimp');

        $this->assertSame(__('Subscribe to newsletter?'), $block->getAttribute('label'));
        $this->assertSame([''], $block->getAttribute('subscriberTags'));
        $this->assertSame([''], $block->getAttribute('defaultAudiences'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('doubleOptIn'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }
}
