<?php

namespace Give\Tests\Unit\Helpers;

use Give_Helper_Payment;
use Give_Payment;
use Give\Tests\TestCase;

/**
 * Class DonationFormVariablePricesDropdownTest
 *
 * Test give_get_form_variable_price_dropdown function output
 *
 * @since 2.19.0
 */
class DonationFormVariablePricesDropdownTest extends TestCase
{
    /**
     * @var Give_Payment
     */
    private $donation;

    public function setUp()
    {
        parent::setUp();

        $this->donation = new Give_Payment(Give_Helper_Payment::create_multilevel_payment());
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testShowCustomDonationLevelChoiceInDropDownIfDonationFormCustomAmountModeActivated()
    {
        give()->form_meta->update_meta($this->donation->form_id, '_give_custom_amount', 'enabled');

        $args = [
            'id' => $this->donation->form_id,
            'selected' => $this->donation->price_id
        ];

        $this->assertContains('Custom', give_get_form_variable_price_dropdown($args));
    }

    public function testShowCustomDonationLevelChoiceInDropDownIfDonationFormCustomAmountModeDisabledAndDonationDonatedWithCustomLevel(
    )
    {
        give()->form_meta->update_meta($this->donation->form_id, '_give_custom_amount', 'disabled');
        $this->donation->price_id = 'custom';
        $this->donation->save();

        $args = [
            'id' => $this->donation->form_id,
            'selected' => $this->donation->price_id
        ];

        $this->assertContains('Custom', give_get_form_variable_price_dropdown($args));
    }

    public function testDoNotShowCustomDonationLevelChoiceInDropDownIfDonationFormCustomAmountModeDisabledAndDonationNotDonatedWithCustomLevel(
    )
    {
        give()->form_meta->update_meta($this->donation->form_id, '_give_custom_amount', 'disabled');
        $this->donation->price_id = 1;
        $this->donation->save();

        $args = [
            'id' => $this->donation->form_id,
            'selected' => $this->donation->price_id
        ];

        $this->assertNotContains('Custom', give_get_form_variable_price_dropdown($args));
    }
}
