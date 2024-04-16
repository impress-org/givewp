<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\Mailchimp;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestMailchimp extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesFromV2FormMeta(): void
    {
        $meta = [
            '_give_mailchimp_custom_label'    => __('Subscribe to newsletter?'),
            '_give_mailchimp_tags'            => ['Animal-Rescue-Campaign', 'Housing-And-Shelter-Campaign'],
            '_give_mailchimp'                 => ['de73f3f82f'],
            '_give_mailchimp_checked_default' => true,
            '_give_mailchimp_send_donation_data'   => true,
            '_give_mailchimp_send_ffm'        => true,
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new Mailchimp($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('givewp/mailchimp');

        $this->assertSame($meta['_give_mailchimp_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_mailchimp_tags'], $block->getAttribute('subscriberTags'));
        $this->assertSame($meta['_give_mailchimp'], $block->getAttribute('defaultAudiences'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesFromGlobalSettings(): void
    {
        $meta = [
            'give_mailchimp_label'           => __('Subscribe to newsletter?'),
            'give_mailchimp_list'            => ['de73f3f82f'],
            'give_mailchimp_checked_default' => true,
            'give_mailchimp_double_opt_in'   => true,
            'give_mailchimp_donation_data'   => true,
            'give_mailchimp_ffm_pass_field'  => true,
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
        $this->assertNull(null, $block->getAttribute('subscriberTags'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('doubleOptIn'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }

    /**
     * @since 3.7.0
     */
    public function testProcessShouldUpdateMailchimpBlockAttributesWhenNoMeta(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $mailchimp = new Mailchimp($payload);

        $mailchimp->process();

        $block = $payload->formV3->blocks->findByName('givewp/mailchimp');

        $this->assertSame(__('Subscribe to newsletter?'), $block->getAttribute('label'));
        $this->assertNull(null, $block->getAttribute('subscriberTags'));
        $this->assertSame([''], $block->getAttribute('defaultAudiences'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('doubleOptIn'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }
}
