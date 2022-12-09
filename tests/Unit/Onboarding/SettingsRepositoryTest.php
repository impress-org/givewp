<?php

namespace Give\Tests\Unit\Onboarding;

use Give\Onboarding\SettingsRepository;
use Give\Tests\TestCase;

final class SettingsRepositoryTest extends TestCase
{

    public function testGetValue()
    {
        $settingsRepository = new SettingsRepository(
            [
                'foo' => 'bar',
            ],
            function () { }
        );
        $this->assertEquals($settingsRepository->get('foo'), 'bar');
    }

    public function testSetValue()
    {
        $settingsRepository = new SettingsRepository([], function () { });
        $settingsRepository->set('foo', 'bar');
        $this->assertEquals($settingsRepository->get('foo'), 'bar');
    }

    public function testSaveCallback()
    {
        $mockCallback = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $mockCallback->expects($this->once())
            ->method('__invoke')
            ->with([]);

        $settingsRepository = new SettingsRepository([], $mockCallback);
        $settingsRepository->save();
    }
}
