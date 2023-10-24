<?php

namespace Give\Tests\Unit\FormMigration\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\FormMigration\Actions\GetMigratedFormId;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class GetMigratedFormIdTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    public function testGetMigratedFormId()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();
        give_update_meta($donationFormV3->id, 'migratedFormId', $donationFormV2->id);

        $this->assertEquals(
            $donationFormV3->id,
            (new GetMigratedFormId)($donationFormV2->id)
        );
    }
}
