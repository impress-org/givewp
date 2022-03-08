<?php

namespace unit\tests\Helpers;

use Give\Helpers\EnqueueScript;
use Give_Unit_Test_Case;

class EnqueueScriptTest extends Give_Unit_Test_Case
{
    public function testScriptRegistration()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script->register();

        $this->assertTrue(wp_script_is($script->getScriptId(), 'registered'));
    }

    public function testScriptEnqueue()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script->enqueue();

        $this->assertTrue(wp_script_is($script->getScriptId()));
    }

    public function testCustomVersion()
    {
        $version = '1.1.0';
        $script = $this->getEnqueueScriptClassObject();
        $script
            ->version($version)
            ->enqueue();

        $this->assertEquals($version, wp_scripts()->registered[$script->getScriptId()]->ver);
    }

    public function testCustomDependencies()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script
            ->dependencies(['jquery'])
            ->enqueue();

        $this->assertContains('jquery', wp_scripts()->registered[$script->getScriptId()]->deps);
    }

    public function testTranslations()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script
            ->registerTranslations()
            ->enqueue();

        $this->assertEquals('give', wp_scripts()->registered[$script->getScriptId()]->textdomain);
    }

    public function testLocalizeData()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script
            ->registerLocalizeData('customJsData', ['success' => 1])
            ->register();

        $this->assertContains('customJsData', wp_scripts()->registered[$script->getScriptId()]->extra['data']);
    }

    public function testLoadScriptInFooter()
    {
        $script = $this->getEnqueueScriptClassObject();
        $script
            ->loadInFooter()
            ->enqueue();

        $this->assertArrayHasKey('group', wp_scripts()->registered[$script->getScriptId()]->extra);
    }

    private function getEnqueueScriptClassObject()
    {
        $uniqueId = wp_generate_password(5);

        return EnqueueScript::make(
            "dummy-script-$uniqueId",
            "assets/dist/js/dummy-script-$uniqueId.js"
        );
    }
}
