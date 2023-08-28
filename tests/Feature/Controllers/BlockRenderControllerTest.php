<?php

namespace Give\Tests\Feature\Controllers;

use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;

class BlockRenderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnIframe()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $viewUrl = (new GenerateDonationFormViewRouteUrl())($donationForm->id);

        $blockRenderController = $this->createMock(
            BlockRenderController::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['loadEmbedScript']); // partial mock gateway by setting methods on the mock builder
                return $mockBuilder->getMock();
            }
        );

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
