<?php

namespace Give\NextGen\DonationForm\Actions;


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
            'givewp-view' => 'donation-form-preview',
            'form-id' => $formId
        ];

        return esc_url(
            add_query_arg(
                $args,
                site_url()
            )
        );
    }
}
