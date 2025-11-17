<?php declare(strict_types=1);

namespace Give\MCP\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * Loads the GiveWP MCP server frontend assets into the dashboard.
 *
 * @since 4.9.0
 */
class RegisterMCPServer
{

    public const HANDLE = 'givewp-mcp-server';

    /**
     * Register the GiveWP MCP server into Elementor's Angie SDK if the Angie plugin
     * is activated and the user has access.
     *
     * @action wp_enqueue_scripts
     * @action admin_enqueue_scripts
     *
     * @since 4.9.0
     *
     * @return void
     */
    public function __invoke(): void
    {
        // The angie plugin isn't installed or activated.
        if ( ! defined('ANGIE_VERSION') ) {
            return;
        }

        // The current user doesn't have permission to use angie.
        if ( ! current_user_can( 'use_angie' ) ) {
            return;
        }

        // If we're not logged in, no reason to include this large script.
        if ( ! is_user_logged_in() ) {
            return;
        }

        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/angieMcp.asset.php');

        wp_register_script(
            self::HANDLE,
            GIVE_PLUGIN_URL . 'build/angieMcp.js',
            array_merge($scriptAsset['dependencies'], ['angie-app']),
            $scriptAsset['version'],
            true
        );

        wp_localize_script( self::HANDLE, 'GiveMcpServerOptions',
            [
                'adminUrl' => admin_url(),
            ]
        );

        wp_enqueue_script(self::HANDLE);
    }
}
