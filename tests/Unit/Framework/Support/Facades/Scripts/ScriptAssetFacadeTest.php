<?php

namespace Give\Tests\Unit\Framework\Support\Facades\Scripts;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class ScriptAssetFacadeTest extends TestCase
{
    /**
     * An unbuilt checkout has no asset files, and scripts must still be registrable there.
     *
     * @since TBD
     */
    public function testFallsBackToThePluginVersionWhenTheAssetFileIsMissing(): void
    {
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/doesNotExist.asset.php');

        $this->assertSame(['dependencies' => [], 'version' => GIVE_VERSION], $asset);
    }
}
