<?php

namespace Give\Subscriptions\Actions;

use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;
use Give\Subscriptions\ListTable\SubscriptionsListTable;

/**
 * @since 4.8.0
 */
class LoadSubscriptionsListTableAssets
{
    /**
     * @since 2.27.1 Pass dismissed recommendations to the localize script
     * @since 2.20.0
     */
    public function __invoke()
    {
        $handleName = 'give-admin-subscriptions';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR.'build/assets/dist/js/give-admin-subscriptions.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL.'build/assets/dist/js/give-admin-subscriptions.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script($handleName, 'GiveSubscriptions', [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/subscriptions')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'forms' => $this->getForms(),
            'table' => give(SubscriptionsListTable::class)->toArray(),
            'adminUrl' => admin_url(),
            'paymentMode' => give_is_test_mode(),
            'pluginUrl' => GIVE_PLUGIN_URL,
        ]);

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL.'build/assets/dist/js/give-admin-subscriptions.css',
            [],
            $asset['version']
        );
    }

    /**
     * Retrieve a list of donation forms to populate the form filter dropdown
     *
     * @since 2.24.0
     *
     * @return array
     */
    private function getForms()
    {
        $options = DB::table('posts')
            ->select(
                ['ID', 'value'],
                ['post_title', 'text']
            )
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', ['publish', 'draft', 'pending', 'private'])
            ->getAll(ARRAY_A);

        return array_merge([
            [
                'value' => '0',
                'text' => __('Any', 'give'),
            ],
        ], $options);
    }
}
