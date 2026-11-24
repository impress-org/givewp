<?php

namespace Give\Framework\ListTable\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * Registers the shared list table chunk that every admin list screen loads.
 *
 * Webpack emits it as a split chunk that no entry enqueues on its own, so the handle is registered
 * here and each list screen adds it as a dependency. The chunk only defines modules; the WordPress
 * packages it uses are dependencies of each list screen's own script. Its CSS stays in each
 * screen's own stylesheet.
 *
 * @since TBD
 */
class RegisterListTableAssets
{
    public const HANDLE = 'givewp-list-table';

    /**
     * @since TBD
     */
    public function __invoke()
    {
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/givewp-list-table.asset.php');

        wp_register_script(
            self::HANDLE,
            GIVE_PLUGIN_URL . 'build/givewp-list-table.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Counts in the text of the list table follow the admin's locale, the way number_format_i18n() writes them.
        wp_localize_script(self::HANDLE, 'GiveListTableNumbers', $this->numbers());

        // The shared chunk holds the list table's own strings, and WordPress looks translations up by handle.
        Language::setScriptTranslations(self::HANDLE);
    }

    /**
     * The separators of the signed-in user's locale. This is configuration, not records.
     *
     * @since TBD
     *
     * @return array{thousandsSep: string, decimalPoint: string}
     */
    private function numbers(): array
    {
        global $wp_locale;

        return [
            'thousandsSep' => (string)($wp_locale->number_format['thousands_sep'] ?? ','),
            'decimalPoint' => (string)($wp_locale->number_format['decimal_point'] ?? '.'),
        ];
    }
}
