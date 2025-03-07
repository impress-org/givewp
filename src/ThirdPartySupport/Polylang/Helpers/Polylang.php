<?php

namespace Give\ThirdPartySupport\Polylang\Helpers;

/**
 * @since 3.22.0
 */
class Polylang
{
    /**
     * @since 3.22.0
     */
    public static function getLocale($field = 'slug'): string
    {
        $locale = get_locale();

        if (function_exists('pll_current_language') && function_exists('PLL')) {
            $pllCurrentLangCode = pll_current_language($field);
            $pllCurrentLang = PLL()->model->get_language($pllCurrentLangCode);

            $locale = $pllCurrentLang && $pllCurrentLang->locale != $locale ? $pllCurrentLang->locale : $locale;
        }

        return $locale;
    }
}
