<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Routes\Route;

/**
 * Data the external embed script reads from window.givewpDonationFormEmbed.
 * Runs when the script is requested, so the strings are in the site's locale
 * and the URLs reflect the site's current home URL.
 *
 * Everything the script needs to know about the WordPress site travels here
 * rather than being hardcoded in the bundle: the home URL, the routes the
 * iframe loads, the form's own page, and the parameters an offsite gateway
 * return carries. The script only appends per-embed values such as the form
 * id.
 *
 * @since TBD
 */
class GetExternalEmbedScriptData
{
    /**
     * @since TBD
     */
    public function __invoke(): array
    {
        return [
            'homeUrl' => home_url('/'),
            'formViewUrl' => esc_url_raw(Route::url('donation-form-view')),
            'receiptViewUrl' => esc_url_raw(Route::url('donation-confirmation-receipt-view')),
            'formPageUrl' => (new GenerateDonationFormPageUrl())(),
            /*
             * The offsite gateway return flow, as GenerateDonationConfirmationReceiptUrl
             * builds it: the listener params that must match, and the params carrying
             * the embed and receipt ids.
             */
            'receiptReturn' => [
                'match' => [
                    'givewp-event' => 'donation-completed',
                    'givewp-listener' => 'show-donation-confirmation-receipt',
                ],
                'embedIdParam' => 'givewp-embed-id',
                'receiptIdParam' => 'givewp-receipt-id',
            ],
            'i18n' => [
                'donate' => __('Donate', 'give'),
                'loading' => __('Loading', 'give'),
                'formTitle' => __('Donation Form', 'give'),
                'openForm' => __('Open donation form', 'give'),
                'close' => __('Close', 'give'),
            ],
        ];
    }
}
