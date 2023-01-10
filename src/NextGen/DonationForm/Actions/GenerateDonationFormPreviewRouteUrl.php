<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\Framework\Routes\Route;

/**
 * @unreleased
 */
class GenerateDonationFormPreviewRouteUrl
{
    /**
     * @unreleased
     */
    public function __invoke(int $formId): string
    {
        $args = [
            'form-id' => $formId
        ];

        return esc_url(
            add_query_arg(
                $args,
                Route::url('donation-form-view-preview')
            )
        );
    }
}
