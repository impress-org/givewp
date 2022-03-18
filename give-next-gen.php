<?php
namespace Give;

use Give\Addon\Activation;
use Give\Addon\Environment;
use Give\NextGen\AddonServiceProvider;

/**
 * Plugin Name:         Give - Next Gen
 * Plugin URI:          https://givewp.com/addons/BOILERPLATE/
 * Description:         A feature plugin for the next generation GiveWP donation forms.
 * Version:             1.0.0
 * Requires at least:   4.9
 * Requires PHP:        5.6
 * Author:              GiveWP
 * Author URI:          https://givewp.com/
 * Text Domain:         give
 * Domain Path:         /languages
 */
defined('ABSPATH') or exit;

// Add-on name
define('GIVE_NEXT_GEN_NAME', 'Give - Next Gen');

// Versions
define('GIVE_NEXT_GEN_VERSION', '1.0.0');
define('GIVE_NEXT_GEN_MIN_GIVE_VERSION', '2.8.0');

// Add-on paths
define('GIVE_NEXT_GEN_FILE', __FILE__);
define('GIVE_NEXT_GEN_DIR', plugin_dir_path(GIVE_NEXT_GEN_FILE));
define('GIVE_NEXT_GEN_URL', plugin_dir_url(GIVE_NEXT_GEN_FILE));
define('GIVE_NEXT_GEN_BASENAME', plugin_basename(GIVE_NEXT_GEN_FILE));

require 'vendor/autoload.php';

// Activate add-on hook.
register_activation_hook(GIVE_NEXT_GEN_FILE, [Activation::class, 'activateAddon']);

// Deactivate add-on hook.
register_deactivation_hook(GIVE_NEXT_GEN_FILE, [Activation::class, 'deactivateAddon']);

// Uninstall add-on hook.
register_uninstall_hook(GIVE_NEXT_GEN_FILE, [Activation::class, 'uninstallAddon']);

// Register the add-on service provider with the GiveWP core.
add_action(
    'before_give_init',
    function () {
        // Check Give min required version.
        if (Environment::giveMinRequiredVersionCheck()) {
            give()->registerServiceProvider(AddonServiceProvider::class);
        }
    }
);

// Check to make sure GiveWP core is installed and compatible with this add-on.
add_action('admin_init', [Environment::class, 'checkEnvironment']);
