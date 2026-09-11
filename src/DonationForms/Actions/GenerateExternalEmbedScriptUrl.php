<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Routes\Route;

/**
 * The URL third-party sites load the external embed script from.
 *
 * @since TBD
 */
class GenerateExternalEmbedScriptUrl
{
    /**
     * Path below the router's script base. Snippets pasted on other sites
     * carry this, so it must not change.
     *
     * @since TBD
     */
    public const URI = 'embed/donation-form/script.js';

    /**
     * @since TBD
     */
    public function __invoke(): string
    {
        return Route::scriptUrl(self::URI);
    }
}
