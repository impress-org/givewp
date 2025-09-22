import register from '@givewp/mcp-server/angie';
import { GiveMcpServerOptions } from '@givewp/mcp-server/angie';

declare const window: {
    GiveMcpServerOptions: GiveMcpServerOptions;
} & Window;

// Register the GiveWP MCP Server with Elementor's angie for use in the dashboard.
register(window.GiveMcpServerOptions).then(() => console.log('GiveWP Angie MCP connection successful'));
