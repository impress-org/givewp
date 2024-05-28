<?php

namespace Give\Tests\Unit\FormMigration\Steps;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Models\DonationForm as V2DonationForm;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\StepProcessor;
use Give\FormMigration\Steps\CurrencySwitcher;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class CurrencySwitcherTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testFormWithoutCurrencySwitcherSettingsIsNotMigrated(): void
    {
        // Arrange
        $v2Form = $this->setUpDonationForm();

        // Act
        $v3Form = $this->migrateForm($v2Form);

        // Assert
        $form = DonationForm::find($v3Form->id);
        $this->assertEmpty($form->settings->currencySwitcherSettings);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseGlobalCurrencySwitcherSettingsIsMigrated(): void
    {
        // Arrange
        $attributes = [
            'cs_status' => 'global',
        ];
        $v2Form = $this->setUpDonationForm($attributes);

        // Act
        $v3Form = $this->migrateForm($v2Form);

        // Assert
        $form = DonationForm::find($v3Form->id);
        $this->assertIsArray($form->settings->currencySwitcherSettings);
        $this->assertArrayHasKey('enable', $form->settings->currencySwitcherSettings);
        $this->assertEquals('global', $form->settings->currencySwitcherSettings['enable']);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToDisableCurrencySwitcherIsMigrated(): void
    {
        // Arrange
        $attributes = [
            'cs_status' => 'disabled',
        ];
        $v2Form = $this->setUpDonationForm($attributes);

        // Act
        $v3Form = $this->migrateForm($v2Form);

        // Assert
        $form = DonationForm::find($v3Form->id);
        $this->assertIsArray($form->settings->currencySwitcherSettings);
        $this->assertArrayHasKey('enable', $form->settings->currencySwitcherSettings);
        $this->assertEquals('disabled', $form->settings->currencySwitcherSettings['enable']);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseLocalCurrencySwitcherIsMigrated(): void
    {
        // Arrange
        $attributes = [
            'cs_status' => 'enabled',
            'cs_message' => 'Testing message',
            'give_cs_default_currency' => 'USD',
            'cs_supported_currency' => ['USD', 'EUR'],
        ];
        $v2Form = $this->setUpDonationForm($attributes);

        // Act
        $v3Form = $this->migrateForm($v2Form);

        // Assert
        $form = DonationForm::find($v3Form->id);
        $settings = $form->settings->currencySwitcherSettings;

        $this->assertArrayHasKey('enable', $settings);
        $this->assertEquals('enabled', $settings['enable']);
        $this->assertArrayHasKey('message', $settings);
        $this->assertEquals('Testing message', $settings['message']);
        $this->assertArrayHasKey('defaultCurrency', $settings);
        $this->assertEquals('USD', $settings['defaultCurrency']);
        $this->assertArrayHasKey('supportedCurrencies', $settings);
        $this->assertEquals(['USD', 'EUR'], $settings['supportedCurrencies']);
    }

    /**
     * Sets up and returns a v2 donation form configured with the
     * given attributes being set to the Currency Switcher settings.
     *
     * @unreleased
     *
     * @throws Exception
     */
    private function setUpDonationForm(array $attributes = []): V2DonationForm
    {
        $form = $this->createSimpleDonationForm();

        foreach ($attributes as $key => $value) {
            give_update_meta($form->id, $key, $value);
        }

        return $form;
    }

    /**
     * @unreleased
     */
    private function migrateForm(V2DonationForm $form): DonationForm
    {
        $payload = FormMigrationPayload::fromFormV2($form);
        $processor = new StepProcessor($payload);
        $processor(new CurrencySwitcher($payload));
        $payload->formV3->save();

        return $payload->formV3;
    }
}
