<?php

namespace Give\DonationForms\Actions;


use Give\Framework\Routes\Route;

/**
 * @since 3.0.0
 */
class GenerateDonationFormPreviewRouteUrl
{
    /**
     * @since 3.0.0
     */
    public function __invoke(int $formId): string
    {
        $args = [
            'form-id' => $formId
        ];

        return Route::url('donation-form-view-preview', $args);
    }
}
