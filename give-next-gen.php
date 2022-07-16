<?php

namespace Give;

use Give\Addon\Activation;
use Give\Addon\Environment;
use Give\Addon\ServiceProvider as AddonServiceProvider;
use Give\NextGen\DonationForm\ServiceProvider as DonationFormServiceProvider;
use Give\NextGen\Framework\FormTemplates\ServiceProvider as FormTemplateServiceProvider;
use Give\NextGen\ServiceProvider as NextGenServiceProvider;

/**
 * Plugin Name:         GiveWP - Visual Donation Form Builder
 * Plugin URI:          https://givewp.com/addons/BOILERPLATE/
 * Description:         Create the donation form of your dreams using an easy-to-use visual donation form builder.
 * Version:             1.0.0
 * Requires at least:   5.5
 * Requires PHP:        7.2
 * Author:              GiveWP
 * Author URI:          https://givewp.com/
 * Text Domain:         give
 * Domain Path:         /languages
 */
defined('ABSPATH') or exit;

// Add-on name
define('GIVE_NEXT_GEN_NAME', 'GiveWP - Visual Donation Form Builder');

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
            give()->registerServiceProvider(DonationFormServiceProvider::class);
            give()->registerServiceProvider(NextGenServiceProvider::class);
            give()->registerServiceProvider(FormBuilder\ServiceProvider::class);
            give()->registerServiceProvider(FormTemplateServiceProvider::class);
        }
    }
);

// Check to make sure GiveWP core is installed and compatible with this add-on.
add_action('admin_init', [Environment::class, 'checkEnvironment']);
