<?php

namespace Give\Tests\Feature\Controllers;

use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class BlockRenderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnIframe()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $viewUrl = (new GenerateDonationFormViewRouteUrl())($donationForm->id);

        $blockRenderController = new BlockRenderController();

        $this->assertSame(
            str_replace(
                "<iframe data-givewp-embed src='$viewUrl' data-givewp-embed-id='123' 
                style='width: 1px;min-width: 100%;border: 0;'></iframe>",
                " ",
                ''
            ),
            str_replace($blockRenderController->render(['formId' => $donationForm->id, 'blockId' => '123']), " ", '')
        );
    }
}
