<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;

class ReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe
{
    /**
     * Replace the give_receipt shortcode with the v3 confirmation page iframe.
     *
     * @since 3.16.0
     */
    public function __invoke(string $view): string
    {
        $data = DonationConfirmationReceiptViewRouteData::fromRequest(give_clean($_GET)); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only param on a public page that may be cached; it saves nothing, and a nonce would expire in the cache. give_clean() sanitizes it.

        if ($data->receiptId) {
            $viewUrl = (new GenerateDonationConfirmationReceiptViewRouteUrl())($data->receiptId);
            return "<iframe style='width: 1px;min-width: 100%;border: 0;' data-givewp-embed src='$viewUrl'></iframe>";
        }

        return $view;
    }
}
