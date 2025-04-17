<?php

namespace Give\ThirdPartySupport\WPML\Helpers;

use SitePress;
use WPML_Request;

/**
 * @since 3.22.0
 */
class WPML
{
    /**
     * @since 3.22.0
     */
    public static function getLocale(): string
    {
        $locale = get_locale();

        /**
         * @var WPML_Request $wpml_request_handler
         * @var SitePress    $sitepress
         */
        global $wpml_request_handler, $sitepress;

        if (isset($wpml_request_handler) && isset($sitepress)) {
            $requestedLang = $wpml_request_handler->get_requested_lang();
            $wpmlLocale = $sitepress->get_locale($requestedLang);
            $locale = $wpmlLocale != $locale ? $wpmlLocale : $locale;
        }

        return $locale;
    }
}
