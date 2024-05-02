<?php

namespace Give\Tests\Unit\Tracking\Helpers;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Revenue\Listeners\UpdateRevenueWhenDonationAmountUpdated;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tracking\Helpers\FormBlocks;

/**
 * @since 2.20.1
 */
class FormBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function testFormHasBlockByName()
    {
        $form = DonationForm::factory()->create();
        $form->blocks->insertAfter('givewp/donor-name', new BlockModel('test/form-block-1'));
        $form->save();

        $this->assertTrue(
            FormBlocks::formId($form->id)->hasBlock('test/form-block-1')
        );
    }

    public function testFormDoesNotHaveBlockByName()
    {
        $form = DonationForm::factory()->create();

        $this->assertFalse(
            FormBlocks::formId($form->id)->hasBlock('test/form-block-1')
        );
    }
}

