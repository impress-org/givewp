<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\ActiveCampaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

class TestActiveCampaign extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseGlobalActiveCampaignSettingsMigratesUsingGlobalSettingsWhenGloballyEnabled()
    {
        // Arrange
        $options = [
            'give_activecampaign_globally_enabled' => 'on',
            'give_activecampaign_label' => __('Subscribe to our newsletter?'),
            'give_activecampaign_lists' => ['1', '2'],
            'give_activecampaign_tags' => ['tag 1', 'tag 2'],
            'give_activecampaign_checkbox_default' => true,
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $meta = ['activecampaign_per_form_options' => 'global'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ActiveCampaign::class);

        // Assert
        $block = $v3Form->blocks->findByName('give-activecampaign/activecampaign');
        $this->assertSame($options['give_activecampaign_label'], $block->getAttribute('label'));
        $this->assertSame($options['give_activecampaign_lists'], $block->getAttribute('selectedLists'));
        $this->assertSame($options['give_activecampaign_tags'], $block->getAttribute('selectedTags'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseGlobalActiveCampaignSettingsIsMigratedWithoutActiveCampaignBlockWhenNotGloballyEnabled()
    {
        // Arrange
        give_update_option('give_activecampaign_globally_enabled', 'off');
        $meta = ['activecampaign_per_form_options' => 'global'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ActiveCampaign::class);

        // Assert
        $block = $v3Form->blocks->findByName('give-activecampaign/activecampaign');
        $this->assertNull($block);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToDisableActiveCampaignIsMigratedWithoutActiveCampaignBlock()
    {
        // Arrange
        $meta = ['activecampaign_per_form_options' => 'disabled'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ActiveCampaign::class);

        // Assert
        $block = $v3Form->blocks->findByName('give-activecampaign/activecampaign');
        $this->assertNull($block);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseCustomizedActiveCampaignSettingsIsMigrated()
    {
        // Arrange
        $meta = [
            'activecampaign_per_form_options' => 'customized',
            'give_activecampaign_label' => __('Subscribe to our newsletter?'),
            'give_activecampaign_lists' => ['1', '2'],
            'give_activecampaign_tags' => ['tag 1', 'tag 2'],
            'give_activecampaign_checkbox_default' => true,
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ActiveCampaign::class);

        // Assert
        $block = $v3Form->blocks->findByName('give-activecampaign/activecampaign');
        $this->assertSame($meta['give_activecampaign_label'], $block->getAttribute('label'));
        $this->assertSame($meta['give_activecampaign_lists'], $block->getAttribute('selectedLists'));
        $this->assertSame($meta['give_activecampaign_tags'], $block->getAttribute('selectedTags'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }
}
