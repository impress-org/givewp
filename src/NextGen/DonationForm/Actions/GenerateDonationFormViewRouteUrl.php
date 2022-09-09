<?php

namespace Give\NextGen\DonationForm\Actions;


/**
 * @unreleased
 */
class GenerateDonationFormViewRouteUrl
{
    /**
     * @unreleased
     */
    public function __invoke(int $formId, string $formTemplateId): string
    {
        return esc_url_raw(
            add_query_arg(
                [
                    'givewp-view' => 'donation-form',
                    'form-id' => $formId,
                    'form-template-id' => $formTemplateId
                ],
                home_url()
            )
        );
    }
}
