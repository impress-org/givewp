<?php

namespace Give\Tests\Unit\Framework\ListTable\Actions;

use Give\Framework\ListTable\Actions\RegisterListTableAssets;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class RegisterListTableAssetsTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        wp_deregister_script(RegisterListTableAssets::HANDLE);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testRegistersTheSharedChunkInTheFooter(): void
    {
        give(RegisterListTableAssets::class)();

        $this->assertTrue(wp_script_is(RegisterListTableAssets::HANDLE, 'registered'));

        $script = wp_scripts()->registered[RegisterListTableAssets::HANDLE];

        $this->assertStringEndsWith('build/givewp-list-table.js', $script->src);
        $this->assertSame(1, wp_scripts()->get_data(RegisterListTableAssets::HANDLE, 'group'));
    }
}
