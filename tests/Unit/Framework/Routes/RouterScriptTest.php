<?php

namespace Give\Tests\Unit\Framework\Routes;

use Give\Framework\Routes\Router;
use Give\Framework\Routes\ScriptResponse;
use Give\Tests\TestCase;
use WP;

/**
 * @since TBD
 */
class RouterScriptTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['givewp-route']);
        $this->setPermalinkStructure('');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testScriptUrlWithPrettyPermalinks(): void
    {
        $this->setPermalinkStructure('/%postname%/');

        $this->assertSame(
            home_url('/give/embed/donation-form/script.js'),
            (new Router())->scriptUrl('embed/donation-form/script.js')
        );
    }

    /**
     * @since TBD
     */
    public function testScriptUrlWithIndexPermalinks(): void
    {
        $this->setPermalinkStructure('/index.php/%postname%/');

        $this->assertSame(
            home_url('/index.php/give/embed/donation-form/script.js'),
            (new Router())->scriptUrl('embed/donation-form/script.js')
        );
    }

    /**
     * @since TBD
     */
    public function testScriptUrlWithPlainPermalinks(): void
    {
        $this->setPermalinkStructure('');

        $this->assertSame(
            home_url('/?givewp-route=embed/donation-form/script.js'),
            (new Router())->scriptUrl('embed/donation-form/script.js')
        );
    }

    /**
     * @since TBD
     */
    public function testMatchesPrettyPathOrRouteQueryVar(): void
    {
        $router = new Router();
        $wp = new WP();

        $wp->request = 'give/embed/donation-form/script.js';
        $this->assertTrue($router->isScriptRequested($wp, 'embed/donation-form/script.js'));

        $wp->request = 'give/some-form';
        $this->assertFalse($router->isScriptRequested($wp, 'embed/donation-form/script.js'));

        $_GET['givewp-route'] = 'embed/donation-form/script.js';
        $this->assertTrue($router->isScriptRequested($wp, 'embed/donation-form/script.js'));
        $this->assertFalse($router->isScriptRequested($wp, 'embed/other.js'));
    }

    /**
     * @since TBD
     */
    public function testEtagIsTheQuotedAssetVersion(): void
    {
        $version = require GIVE_PLUGIN_DIR . 'build/donationFormExternalEmbed.asset.php';

        $this->assertSame('"' . $version['version'] . '"', (new ScriptResponse(GIVE_PLUGIN_DIR . 'build/donationFormExternalEmbed.js'))->etag());
    }

    /**
     * @since TBD
     */
    public function testIfNoneMatchToleratesWeakAndGzipValidators(): void
    {
        $response = new ScriptResponse(GIVE_PLUGIN_DIR . 'build/donationFormExternalEmbed.js');

        $this->assertTrue($response->matchesIfNoneMatch('"abc"', '"abc"'));
        $this->assertTrue($response->matchesIfNoneMatch('"abc"', 'W/"abc"'));
        $this->assertTrue($response->matchesIfNoneMatch('"abc"', '"abc-gzip"'));
        $this->assertTrue($response->matchesIfNoneMatch('"abc"', '"old", "abc"'));
        $this->assertTrue($response->matchesIfNoneMatch('"abc"', '*'));
        $this->assertFalse($response->matchesIfNoneMatch('"abc"', '"abd"'));
        $this->assertFalse($response->matchesIfNoneMatch('"abc"', ''));
    }

    /**
     * @since TBD
     */
    private function setPermalinkStructure(string $structure): void
    {
        global $wp_rewrite;

        $wp_rewrite->set_permalink_structure($structure);
    }
}
