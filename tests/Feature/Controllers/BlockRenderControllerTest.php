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
            "<iframe data-givewp-embed src='$viewUrl'
                style='width: 1px;min-width: 100%;border: 0;'></iframe>",
            $blockRenderController->render(['formId' => $donationForm->id])
        );
    }
}
