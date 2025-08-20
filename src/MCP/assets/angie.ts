import register from '@givewp/mcp-server/angie';

// Register the GiveWP MCP Server with Elementor's angie for use in the dashboard.
register().then(() => console.log('GiveWP Angie MCP connection successful'));
