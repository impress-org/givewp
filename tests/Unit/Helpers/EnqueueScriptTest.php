<?php

namespace Give\Tests\Unit\Helpers;

use Give\Helpers\EnqueueScript;
use Give\Tests\TestCase;

class EnqueueScriptTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->enqueueScriptClassObject = GiveEnqueueScriptMock::make();
        $this->addonEnqueueScriptClassObject = GiveFfmEnqueueScriptMock::make();
    }

    public function testScriptRegistration()
    {
        $this->enqueueScriptClassObject->register();
        $this->addonEnqueueScriptClassObject->register();

        $this->assertTrue(wp_script_is($this->enqueueScriptClassObject->getScriptId(), 'registered'));
        $this->assertTrue(wp_script_is($this->addonEnqueueScriptClassObject->getScriptId(), 'registered'));
    }

    public function testScriptEnqueue()
    {
        $this->enqueueScriptClassObject->enqueue();
        $this->addonEnqueueScriptClassObject->enqueue();

        $this->assertTrue(wp_script_is($this->enqueueScriptClassObject->getScriptId()));
        $this->assertTrue(wp_script_is($this->addonEnqueueScriptClassObject->getScriptId()));
    }

    public function testCustomVersion()
    {
        $version = '1.1.0';
        $this->enqueueScriptClassObject
            ->version($version)
            ->enqueue();

        $this->addonEnqueueScriptClassObject
            ->version($version)
            ->enqueue();

        $this->assertEquals($version, wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->ver);
        $this->assertEquals(
            $version,
            wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->ver
        );
    }

    public function testCustomDependencies()
    {
        $this->enqueueScriptClassObject
            ->dependencies(['jquery'])
            ->enqueue();
        $this->addonEnqueueScriptClassObject
            ->dependencies(['wp-i18n', 'jquery-ui'])
            ->enqueue();

        $this->assertCount(1, wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->deps);
        $this->assertContains('jquery', wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->deps);
        $this->assertCount(2, wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->deps);
        $this->assertContains(
            'jquery-ui',
            wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->deps
        );
        $this->assertContains(
            'wp-i18n',
            wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->deps
        );
    }

    public function testTranslations()
    {
        $this->enqueueScriptClassObject
            ->registerTranslations()
            ->enqueue();
        $this->addonEnqueueScriptClassObject
            ->registerTranslations()
            ->enqueue();

        $this->assertEquals(
            'give',
            wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->textdomain
        );
        $this->assertEquals(
            'give-ffm',
            wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->textdomain
        );
    }

    public function testLocalizeData()
    {
        $this->enqueueScriptClassObject
            ->registerLocalizeData('coreJsData', ['success' => 1])
            ->register();
        $this->addonEnqueueScriptClassObject
            ->registerLocalizeData('giveFfmJsData', ['success' => 1])
            ->register();

        $this->assertContains('coreJsData', wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->extra['data']);
        $this->assertContains('giveFfmJsData', wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->extra['data']);
    }

    public function testLoadScriptInFooter()
    {
        $this->enqueueScriptClassObject
            ->loadInFooter()
            ->enqueue();
        $this->addonEnqueueScriptClassObject
            ->loadInFooter()
            ->enqueue();

        $this->assertArrayHasKey('group', wp_scripts()->registered[$this->enqueueScriptClassObject->getScriptId()]->extra);
        $this->assertArrayHasKey('group', wp_scripts()->registered[$this->addonEnqueueScriptClassObject->getScriptId()]->extra);
    }
}


// Add version to script otherwise will get fatal error.
// Because EnqueueScript::getAssetFileData use filemtime function to get file version if asset file is not defined.
// And we are using script which does not exist.

class GiveEnqueueScriptMock
{
    public static function make()
    {
        $uniqueId = wp_generate_password(5);

        return EnqueueScript::make(
            "dummy-script-$uniqueId",
            "assets/dist/js/dummy-script-$uniqueId.js"
        )->version('1.0.0');
    }
}

class GiveFfmEnqueueScriptMock
{
    public static function make()
    {
        $uniqueId = wp_generate_password(5);

        return (new \Give\Framework\EnqueueScript(
            "dummy-script-$uniqueId",
            "assets/dist/js/dummy-script-$uniqueId.js",
            'wp-content/ffm',
            'http://give.test/wp-content/plugin/ffm',
            'give-ffm'
        ))->version('1.0.0');
    }
}
