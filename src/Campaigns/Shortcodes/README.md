# Campaign Shortcodes

This directory contains shortcode implementations for GiveWP campaign blocks, allowing blocks to be used outside of the block editor context.

## Shortcode Render Controller

The `ShortcodeRenderController` class provides utilities for rendering blocks in shortcode context while maintaining compatibility with WordPress block functions like `get_block_wrapper_attributes()`.

### Basic Usage

```php
use Give\Campaigns\Shortcodes\ShortcodeRenderController;

// Simple render with block context
$html = ShortcodeRenderController::renderWithBlockContext(
    $renderFilePath,
    'givewp/campaign-stats-block',
    $attributes
);
```

### Advanced Usage with Extra Variables

```php
// Pass additional variables to the render file
$html = ShortcodeRenderController::renderWithBlockContext(
    $renderFilePath,
    'givewp/campaign-stats-block',
    $attributes,
    [
        'campaign' => $campaignModel,
        'customData' => $someData,
    ]
);
```



## Why This Controller is Needed

WordPress block functions like `get_block_wrapper_attributes()` expect certain static properties to be set during block rendering. When rendering blocks through shortcodes (outside the normal block editor context), these properties are not set, causing PHP warnings and missing functionality.

The `ShortcodeRenderController` solves this by:

1. Creating proper `WP_Block` instances
2. Setting up the correct block context in `WP_Block_Supports::$block_to_render`
3. Restoring the previous context after rendering
4. Providing fallbacks for edge cases

## Migrating Existing Shortcodes

To update existing shortcode classes to use the controller:

### Before
```php
public function renderShortcode($atts): string
{
    $this->loadAssets();
    $attributes = $this->parseAttributes($atts);

    $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/MyBlock/render.php';

    ob_start();
    include $renderFile;
    return ob_get_clean();
}
```

### After
```php
public function renderShortcode($atts): string
{
    $this->loadAssets();
    $attributes = $this->parseAttributes($atts);

    $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/MyBlock/render.php';

    return ShortcodeRenderController::renderWithBlockContext(
        $renderFile,
        'givewp/my-block-name',
        $attributes
    );
}
```

## Best Practices

1. **Always use the controller** when rendering block files from shortcodes
2. **Pass the correct block name** as registered in WordPress
3. **Include extra variables** when the render file needs additional data beyond attributes
4. **Follow the existing naming convention** for block names and directory structures
