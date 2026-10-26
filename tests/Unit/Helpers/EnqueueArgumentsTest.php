<?php

namespace Give\Tests\Unit\Helpers;

use Give\API\REST\V3\Entities\Actions\RegisterPublicEntities;
use Give\Donors\Actions\LoadDonorAdminOptions;
use Give\Tests\TestCase;

/**
 * @since 4.19.1
 */
final class EnqueueArgumentsTest extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function testPublicEntitiesScriptLoadsInFooterWithDeferStrategy(): void
    {
        (new RegisterPublicEntities())();

        $script = wp_scripts()->query('givewp-entities-public');

        $this->assertNotFalse($script);
        $this->assertNotEmpty($script->ver);
        $this->assertSame(1, $script->extra['group']);
        $this->assertSame('defer', $script->extra['strategy']);
    }

    /**
     * @since 4.19.1
     */
    public function testHandleOnlyOptionsScriptHasExplicitVersionAndStaysInHead(): void
    {
        (new LoadDonorAdminOptions())();

        $script = wp_scripts()->query('give-donor-options');

        $this->assertNotFalse($script);
        $this->assertSame(GIVE_VERSION, $script->ver);
        $this->assertArrayNotHasKey('group', $script->extra);
    }
}
