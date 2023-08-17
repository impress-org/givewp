<?php

namespace Give\Tests\Unit\FormMigration;

use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class FunctionsTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    public function testIsFormRedirected()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'redirectedFormId', $donationFormV2->id);

        $formId = $donationFormV2->id;

        give_redirect_form_id($formId);

        $this->assertEquals($donationFormV3->id, $formId);
    }

    public function testIsFormRedirectedWithAdditionalReference()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'redirectedFormId', $donationFormV2->id);

        $formId = $donationFormV2->id;
        $atts['id'] = $donationFormV2->id;

        give_redirect_form_id($formId, $atts['id']);

        $this->assertEquals($donationFormV3->id, $formId);
        $this->assertEquals($donationFormV3->id, $atts['id']);
    }

    public function testIsFormMigrated()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'migratedFormId', $donationFormV2->id);

        $this->assertTrue(give_is_form_migrated($donationFormV2->id));
    }

    public function testIsFormNotMigrated()
    {
        $donationFormV2 = $this->createSimpleDonationForm();

        $this->assertFalse(give_is_form_migrated($donationFormV2->id));
    }

    public function testIsFormDonationsTransferred()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'transferredFormId', $donationFormV2->id);

        $this->assertTrue(give_is_form_donations_transferred($donationFormV2->id));
    }

    public function testIsFormDonationsNotTransferred()
    {
        $donationFormV2 = $this->createSimpleDonationForm();

        $this->assertFalse(give_is_form_donations_transferred($donationFormV2->id));
    }
}
