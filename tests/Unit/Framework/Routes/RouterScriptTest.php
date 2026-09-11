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
     * A built script and its asset file, so the tests do not depend on build/.
     */
    private string $script;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->script = tempnam(sys_get_temp_dir(), 'givewp-script') . '.js';
        file_put_contents($this->script, 'console.log("embed");');
        file_put_contents(
            preg_replace('/\.js$/', '.asset.php', $this->script),
            "<?php return ['dependencies' => [], 'version' => 'abc123'];"
        );
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['givewp-route']);
        $this->setPermalinkStructure('');
        @unlink($this->script);
        @unlink(preg_replace('/\.js$/', '.asset.php', $this->script));

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
        $this->assertSame('"abc123"', (new ScriptResponse($this->script))->etag());
    }

    /**
     * @since TBD
     */
    public function testLocalizedDataIsPrintedAheadOfTheScriptAndVersionsTheEtag(): void
    {
        $response = new ScriptResponse(GIVE_PLUGIN_DIR . 'build/donationFormExternalEmbed.js');
        $plainEtag = $response->etag();

        $response->localize('givewpTest', static function (): array {
            return ['i18n' => ['donate' => 'Spenden']];
        });
        $prologue = $response->prologue();

        $this->assertSame("var givewpTest = {\"i18n\":{\"donate\":\"Spenden\"}};\n", $prologue);
        $this->assertNotSame($plainEtag, $response->etag($prologue));
        $this->assertSame($response->etag($prologue), $response->etag($prologue));
    }

    /**
     * @since TBD
     */
    public function testIfNoneMatchToleratesWeakAndGzipValidators(): void
    {
        $response = new ScriptResponse($this->script);

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
