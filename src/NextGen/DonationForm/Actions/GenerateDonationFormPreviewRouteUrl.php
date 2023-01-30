<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\Framework\Routes\Route;

/**
 * @since 0.1.0
 */
class GenerateDonationFormPreviewRouteUrl
{
    /**
     * @since 0.1.0
     */
    public function __invoke(int $formId): string
    {
        $args = [
            'form-id' => $formId
        ];

        return esc_url(Route::url('donation-form-view-preview', $args));
    }
}
