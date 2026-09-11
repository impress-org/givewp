<?php

namespace Give\Tests\Unit\DonationForms\Routes;

use Give\DonationForms\Routes\ExternalEmbedScriptRoute;
use Give\Tests\TestCase;
use WP;

/**
 * @since TBD
 */
class ExternalEmbedScriptRouteTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET[ExternalEmbedScriptRoute::QUERY_VAR]);
        update_option('permalink_structure', '');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testUrlUsesPrettyPathWhenPermalinksAreEnabled(): void
    {
        update_option('permalink_structure', '/%postname%/');

        $this->assertSame(home_url('/give/embed/donation-form/script.js'), ExternalEmbedScriptRoute::url());
    }

    /**
     * @since TBD
     */
    public function testUrlFallsBackToQueryVarWithoutPermalinks(): void
    {
        update_option('permalink_structure', '');

        $this->assertSame(
            home_url('/?givewp-embed-script=donation-form'),
            ExternalEmbedScriptRoute::url()
        );
    }

    /**
     * @since TBD
     */
    public function testMatchesPrettyPathRequest(): void
    {
        $wp = new WP();
        $wp->request = 'give/embed/donation-form/script.js';

        $this->assertTrue((new ExternalEmbedScriptRoute())->isRequested($wp));
    }

    /**
     * @since TBD
     */
    public function testMatchesQueryVarRequest(): void
    {
        $_GET[ExternalEmbedScriptRoute::QUERY_VAR] = 'donation-form';
        $wp = new WP();
        $wp->request = '';

        $this->assertTrue((new ExternalEmbedScriptRoute())->isRequested($wp));
    }

    /**
     * @since TBD
     */
    public function testIgnoresOtherRequests(): void
    {
        $wp = new WP();
        $wp->request = 'give/some-form';

        $this->assertFalse((new ExternalEmbedScriptRoute())->isRequested($wp));

        $_GET[ExternalEmbedScriptRoute::QUERY_VAR] = 'something-else';

        $this->assertFalse((new ExternalEmbedScriptRoute())->isRequested($wp));
    }
}
