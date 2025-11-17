<?php

namespace Give\API\REST\V3\Entities\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class RegisterPublicEntities
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $handleName = 'givewp-entities-public';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/entitiesPublic.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/entitiesPublic.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        // Prevent unnecessary current-user fetch/noise for logged-out visitors.
        if (!is_user_logged_in()) {
            $this->disableCurrentUserFetch();
        }

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }

    /**
     * Adds an inline wp.apiFetch middleware that prevents unauthenticated requests
     * to the current-user REST endpoint from being sent.
     *
     * Why
     * - Core packages (e.g., preferences-persistence, core-data) may call
     *   `/wp/v2/users/me` on the front end. When the visitor is not logged in,
     *   WordPress responds with 401 `rest_not_logged_in`, which creates noisy
     *   console errors.
     *
     * What it does
     * - Hooks into the global `wp.apiFetch` pipeline and normalizes the request
     *   target from `options.path` or `options.url` to a path+query string.
     * - If the target contains `/wp/v2/users/me` (with or without query
     *   parameters), it immediately resolves with `null`, avoiding the network
     *   call and the resulting console error.
     * - Otherwise, it delegates to the next middleware.
     *
     * Scope
     * - Only applied for logged-out visitors (see caller).
     * - Does not affect logged-in users or other endpoints.
     *
     * @unreleased
     */
    private function disableCurrentUserFetch()
    {
        wp_add_inline_script(
            'wp-api-fetch',
            '(function(){if(!window.wp||!wp.apiFetch||!wp.apiFetch.use){return;}wp.apiFetch.use(function(options,next){var p=String((options&&(options.path||options.url))||"");try{var u=new URL(p,window.location.origin);p=(u.pathname||"")+(u.search||"");}catch(e){}if(p.indexOf("/wp/v2/users/me")!==-1){return Promise.resolve(null);}return next(options);});})();',
            'after'
        );
    }
}
