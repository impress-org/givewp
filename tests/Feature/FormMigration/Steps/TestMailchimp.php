<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\Mailchimp;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

class TestMailchimp extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testMailchimpSettingsAreMigratedWhenGloballyEnabledAndNotDisabledForSpecificFormUsingGlobalSettings(): void
    {
        // Arrange
        $options = [
            'give_mailchimp_show_checkout_signup' => 'on',
            'give_mailchimp_label' => __('Subscribe to newsletter?'),
            'give_mailchimp_list' => ['de73f3f82f'],
            'give_mailchimp_checked_default' => true,
            'give_mailchimp_double_opt_in' => true,
            'give_mailchimp_donation_data' => true,
            'give_mailchimp_ffm_pass_field' => true,
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $v2Form = $this->createSimpleDonationForm();

        // Act
        $v3Form = $this->migrateForm($v2Form, Mailchimp::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/mailchimp');
        $this->assertSame($options['give_mailchimp_label'], $block->getAttribute('label'));
        $this->assertSame($options['give_mailchimp_list'], $block->getAttribute('defaultAudiences'));
        $this->assertNull(null, $block->getAttribute('subscriberTags'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('doubleOptIn'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }

    /**
     * @since 3.16.0
     */
    public function testMailchimpSettingsAreMigratedWhenGloballyEnabledAndNotDisabledForSpecificFormUsingFormSettings(): void
    {
        // Arrange
        give_update_option('give_mailchimp_show_checkout_signup', 'on');
        $meta = [
            '_give_mailchimp_custom_label' => __('Subscribe to newsletter?'),
            '_give_mailchimp_tags' => ['Animal-Rescue-Campaign', 'Housing-And-Shelter-Campaign'],
            '_give_mailchimp' => ['de73f3f82f'],
            '_give_mailchimp_checked_default' => true,
            '_give_mailchimp_send_donation_data' => true,
            '_give_mailchimp_send_ffm' => true,
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, Mailchimp::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/mailchimp');
        $this->assertSame($meta['_give_mailchimp_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_mailchimp_tags'], $block->getAttribute('subscriberTags'));
        $this->assertSame($meta['_give_mailchimp'], $block->getAttribute('defaultAudiences'));
        $this->assertTrue(true, $block->getAttribute('checked'));
        $this->assertTrue(true, $block->getAttribute('sendDonationData'));
        $this->assertTrue(true, $block->getAttribute('sendFFMData'));
    }

    /**
     * @since 3.16.0
     */
    public function testMailchimpSettingsAreNotMigratedWhenNotGloballyEnabledOrEnabledPerForm()
    {
        // Arrange
        give_update_option('give_mailchimp_show_checkout_signup', 'off');
        $meta = ['_give_mailchimp_enable' => 'false'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, Mailchimp::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/mailchimp');
        $this->assertNull($block);
    }

    /**
     * @since 3.16.0
     */
    public function testMailchimpSettingsAreNotMigratedWhenGloballyEnabledButDisabledForSpecificForm()
    {
        // Arrange
        give_update_option('give_mailchimp_show_checkout_signup', 'off');
        $meta = ['_give_mailchimp_disable' => 'false'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, Mailchimp::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/mailchimp');
        $this->assertNull($block);
    }
}
