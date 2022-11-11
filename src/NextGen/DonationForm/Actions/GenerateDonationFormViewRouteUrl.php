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
    public function __invoke(int $formId, array $formSettings = []): string
    {
        $args = [
            'givewp-view' => 'donation-form',
            'form-id' => $formId
        ];

        if (!empty($formSettings)) {
            $args['form-settings'] = $formSettings;
        }

        return esc_url_raw(
            add_query_arg(
                $args,
                home_url()
            )
        );
    }
}
