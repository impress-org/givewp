<?php declare(strict_types=1);

namespace Give\MCP;

use Give\Helpers\Hooks;
use Give\MCP\Actions\RegisterMCPServer;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * Register Give/Elementor's Angie MCP Server related functionality.
 *
 * @link https://modelcontextprotocol.io/docs/getting-started/intro
 *
 * @since 4.9.0
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(): void {
        give()->singleton(RegisterMCPServer::class);
    }

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        Hooks::addAction('wp_enqueue_scripts', RegisterMCPServer::class);
        Hooks::addAction('admin_enqueue_scripts', RegisterMCPServer::class);
    }
}
