<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\Framework\Routes\Route;

/**
 * @since 0.1.0
 */
class GenerateDonationFormViewRouteUrl
{
    /**
     * @since 0.1.0
     */
    public function __invoke(int $formId): string
    {
        $args = [
            'form-id' => $formId
        ];

        return esc_url_raw(Route::url('donation-form-view', $args));
    }
}
