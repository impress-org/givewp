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
        give_update_meta($donationFormV3->id, 'transferredFormId', $donationFormV2->id);

        $formId = $donationFormV2->id;

        give_redirect_form_id($formId);

        $this->assertEquals($donationFormV3->id, $formId);
    }

    public function testIsFormRedirectedWithAdditionalReference()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'transferredFormId', $donationFormV2->id);

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

    public function testIsFormTransferred()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'transferredFormId', $donationFormV2->id);

        $this->assertTrue(give_is_form_transferred($donationFormV2->id));
    }

    public function testIsFormNotTransferred()
    {
        $donationFormV2 = $this->createSimpleDonationForm();

        $this->assertFalse(give_is_form_transferred($donationFormV2->id));
    }

    /**
     * The old name must keep updating by-reference arguments and log one deprecation notice.
     *
     * @since TBD
     */
    public function testDeprecatedRedirectNameKeepsReferencesAndLogsOneNotice()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'transferredFormId', $donationFormV2->id);
        give_update_meta($donationFormV3->id, 'migratedFormId', $donationFormV2->id);

        $notices = [];
        add_filter('give_deprecated_function_trigger_error', '__return_true');
        set_error_handler(function ($level, $message) use (&$notices) {
            $notices[] = $message;

            return true;
        }, E_USER_NOTICE);

        $formId = $donationFormV2->id;
        $atts['id'] = $donationFormV2->id;

        try {
            _give_redirect_form_id($formId, $atts['id']);
            $migrated = _give_is_form_migrated($donationFormV2->id);
            $transferred = _give_is_form_transferred($donationFormV2->id);
        } finally {
            restore_error_handler();
            remove_filter('give_deprecated_function_trigger_error', '__return_true');
        }

        $this->assertEquals($donationFormV3->id, $formId);
        $this->assertEquals($donationFormV3->id, $atts['id']);
        $this->assertTrue($migrated);
        $this->assertTrue($transferred);
        $this->assertCount(3, $notices);
    }
}
