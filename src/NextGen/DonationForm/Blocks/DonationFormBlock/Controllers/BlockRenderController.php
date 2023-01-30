<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock\Controllers;

use Give\Framework\EnqueueScript;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use Give\NextGen\DonationForm\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\Routes\RouteListener;

class BlockRenderController
{
    /**
     * @since 0.1.0
     *
     * @return string|null
     */
    public function render(array $attributes)
    {
        // return early if we're still inside the editor to avoid server side effects
        if (!empty($_REQUEST['post']) || !empty($_REQUEST['action']) || !empty($_REQUEST['_locale'])) {
            return null;
        }

        $blockAttributes = BlockAttributes::fromArray($attributes);

        if (!$blockAttributes->formId) {
            return null;
        }

        $this->loadEmbedScript();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($blockAttributes->formId);

        $embedId = $blockAttributes->blockId ?? '';

        $viewUrl = $this->getViewUrl($donationForm, $embedId);

        /**
         * Note: iframe-resizer uses querySelectorAll so using a data attribute makes the most sense to target.
         * It will also generate a dynamic ID - so when we have multiple embeds on a page there will be no conflict.
         */
        return "<iframe data-givewp-embed src='$viewUrl' data-givewp-embed-id='$embedId' style='width: 1px;min-width: 100%;border: 0;'></iframe>";
    }

    /**
     * If the page loads with our receipt route listener args then we need to render the receipt.
     *
     * @since 0.1.0
     */
    protected function shouldDisplayDonationConfirmationReceipt(string $embedId): bool
    {
        $routeListener = new RouteListener(
            'donation-completed',
            'show-donation-confirmation-receipt'
        );

        return $routeListener->isValid($_GET, function ($request) use ($embedId) {
            $isset = isset($request['givewp-embed-id'], $request['givewp-receipt-id']);

            return $isset && $request['givewp-embed-id'] === $embedId && DonationConfirmationReceiptViewRouteData::isReceiptIdValid(
                    $request['givewp-receipt-id']
                );
        });
    }

    /**
     * Get the iframe URL.
     * This could either be the donation form view or the donation confirmation receipt view.
     *
     * @since 0.1.0
     */
    private function getViewUrl(DonationForm $donationForm, string $embedId): string
    {
        if ($this->shouldDisplayDonationConfirmationReceipt($embedId)) {
            $receiptId = give_clean($_GET['givewp-receipt-id']);

            return (new GenerateDonationConfirmationReceiptViewRouteUrl())($receiptId);
        }

        return (new GenerateDonationFormViewRouteUrl())($donationForm->id);
    }

    /**
     *
     * Load embed givewp script to resize iframe
     * @see https://github.com/davidjbradshaw/iframe-resizer
     *
     * @since 0.1.0
     */
    private function loadEmbedScript()
    {
        (new EnqueueScript(
            'givewp-donation-form-embed',
            'build/donationFormEmbed.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();
    }
}
