<?php

namespace Give\DonationForms\Actions;

/**
 * Data the external embed script reads from window.givewpDonationFormEmbed.
 * Runs when the script is requested, so the strings are in the site's locale.
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
