<?php

namespace Give\DonationForms\Actions;

/**
 * The URL of a donation form's own page: the give_forms single, addressed by
 * post type and id so it resolves under every permalink setting. WordPress
 * redirects it to the pretty permalink.
 *
 * Used by the block's new-tab launcher and shipped to the external embed
 * script, which appends the form id itself.
 *
 * @since TBD
 */
class GenerateDonationFormPageUrl
{
    /**
     * @since TBD
     *
     * @param int|null $formId Omit for the base URL without a form id.
     */
    public function __invoke(?int $formId = null): string
    {
        $args = ['post_type' => 'give_forms'];

        if ($formId !== null) {
            $args['p'] = $formId;
        }

        return esc_url_raw(add_query_arg($args, home_url('/')));
    }
}
