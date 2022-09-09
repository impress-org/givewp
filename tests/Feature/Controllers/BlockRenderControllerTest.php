<?php

use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use GiveTests\TestCase;

class BlockRenderControllerTest extends TestCase
{
    /**
     * @unreleased
     * 
     * @return void
     */
     public function testShouldReturnIframe()
    {
        $formId = 1;
        $formTemplateId = 'classic';
        $viewUrl = (new GenerateDonationFormViewRouteUrl())($formId, $formTemplateId);

        $blockRenderController = new BlockRenderController();

        $this->assertSame(
            "<iframe data-givewp-embed src='$viewUrl'
                style='width: 1px;min-width: 100%;border: 0;'></iframe>",
            $blockRenderController->render(['formId' => $formId, 'formTemplateId' => $formTemplateId]));
    }
}
