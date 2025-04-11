<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockBuilder;

class BlockRenderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMultipleEmbedsOnSamePageGenerateUniqueEmbedIds(): void
    {
        $donationForm = DonationForm::factory()->create();

        $render1 = (new BlockRenderController())->render(['formId' => $donationForm->id]);
        $render2 = (new BlockRenderController())->render(['formId' => $donationForm->id]);
        $render3 = (new BlockRenderController())->render(['formId' => $donationForm->id]);

        $this->assertStringContainsString("data-givewp-embed-id='givewp-embed-1'", $render1);
        $this->assertStringContainsString("data-givewp-embed-id='givewp-embed-2'", $render2);
        $this->assertStringContainsString("data-givewp-embed-id='givewp-embed-3'", $render3);
    }

    /**
     * @since 3.0.0
     */
    public function testShouldReturnIframe(): void
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $viewUrl = (new GenerateDonationFormViewRouteUrl())($donationForm->id);

        $blockRenderController = $this->createMockWithCallback(
            BlockRenderController::class,
            function (MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['loadEmbedScript']
                ); // partial mock gateway by setting methods on the mock builder
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
