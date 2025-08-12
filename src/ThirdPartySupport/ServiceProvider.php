<?php

namespace Give\ThirdPartySupport;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\ThirdPartySupport\Polylang\Helpers\Polylang;
use Give\ThirdPartySupport\WPML\Helpers\WPML;

/**
 * @since 3.22.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.22.0
     */
    public function register()
    {
    }

    /**
     * @since 3.22.0
     */
    public function boot()
    {
        /**
         * When in the admin area and WPML or Polylang is installed, retrieve the language
         * selected in the language selector of the WordPress admin bar added by them
         */
        add_filter('givewp_locale', function ($locale) {
            if ( ! is_admin()) {
                return $locale;
            }

            $wpmlLocale = WPML::getLocale();
            if ($wpmlLocale != $locale) {
                return $wpmlLocale;
            }

            $polylangLocale = Polylang::getLocale();
            if ($polylangLocale != $locale) {
                return $polylangLocale;
            }

            return $locale;
        });
    }
}
