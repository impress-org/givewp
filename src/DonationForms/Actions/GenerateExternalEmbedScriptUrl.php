<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Routes\Route;

/**
 * The URL third-party sites load the external embed script from.
 *
 * @since 4.17.0
 */
class GenerateExternalEmbedScriptUrl
{
    /**
     * Path below the router's script base. Snippets pasted on other sites
     * carry this, so it must not change.
     *
     * @since 4.17.0
     */
    public const URI = 'embed/donation-form/script.js';

    /**
     * With a form id the URL names the form (`?form-id=42`), and the script response carries
     * that form's skeleton so the embed can draw it before the form page answers. Without one the
     * script is site-level and the embed shows a spinner until the form page's own skeleton.
     *
     * @since 4.17.0
     */
    public function __invoke(int $formId = 0): string
    {
        return Route::scriptUrl(self::URI, $formId > 0 ? ['form-id' => $formId] : []);
    }
}
