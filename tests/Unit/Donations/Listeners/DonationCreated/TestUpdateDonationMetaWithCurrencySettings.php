<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Donations\Listeners\DonationCreated;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Listeners\DonationCreated\UpdateDonationMetaWithCurrencySettings;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\DonationForm as FieldsAPIDonationForm;
use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @coversDefaultClass UpdateDonationMetaWithCurrencySettings
 */
class TestUpdateDonationMetaWithCurrencySettings extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldUpdateDonationMetaWithCurrencySettings()
    {
        add_action('givewp_donation_form_schema', function (FieldsAPIDonationForm $form) {
            $form->currencySwitcherSettings(
                new CurrencySwitcherSetting('USD', 1, [], 2),
                new CurrencySwitcherSetting('EUR', 2, [], 2),
            );
        });

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        give_update_option('currency', 'USD');

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'amount' => new Money(100, 'EUR'),
            'formId' => $form->id,
        ]);

        $action = new UpdateDonationMetaWithCurrencySettings();
        $action($donation);

        $this->assertEquals('EUR', $donation->amount->getCurrency()->getCode());
        $this->assertEquals(2, $donation->exchangeRate);

        $this->assertEquals('enabled', give_get_meta($donation->id, '_give_cs_enabled', true));
        $this->assertEquals('USD', give_get_meta($donation->id, '_give_cs_base_currency', true));
        $this->assertEquals('0.50', give_get_meta($donation->id, '_give_cs_base_amount', true));
    }

    /**
     * @unreleased
     */
    public function testShouldNotUpdateDonationMetaWithCurrencySettingsIfCurrencySwitcherSettingsAreNotSet()
    {
        $action = new UpdateDonationMetaWithCurrencySettings();
        $donation = Donation::factory()->create();

        $action($donation);

        $this->assertEmpty(give()->payment_meta->get_meta($donation->id, '_give_cs_enabled', true));
        $this->assertEmpty(give()->payment_meta->get_meta($donation->id, '_give_cs_base_currency', true));
        $this->assertEmpty(give()->payment_meta->get_meta($donation->id, '_give_cs_base_amount', true));
    }
}
