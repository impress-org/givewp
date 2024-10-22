<?php

namespace Give\Tests\Unit\Helpers;

use Give\Helpers\Utils;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class UtilsTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testRemoveBackslashes()
    {
        $stringWithoutBackslashes = Utils::removeBackslashes('\\ backslash-bypass.');
        $this->assertTrue(strpos($stringWithoutBackslashes, '\\') === false);

        $stringWithoutBackslashes = Utils::removeBackslashes('\\\\ double-backslash-bypass.');
        $this->assertTrue(strpos($stringWithoutBackslashes, '\\') === false);

        $stringWithoutBackslashes = Utils::removeBackslashes('\\\\\\\\\\\\ multiple-backslash-bypass.');
        $this->assertTrue(strpos($stringWithoutBackslashes, '\\') === false);
    }

    /**
     * @unreleased
     */
    public function testContainsSerializedDataRegex()
    {
        $stringWithSerializedDataRegex = 'Lorem ipsum dolor sit amet, {a:2:{i:0;s:5:\"hello\";i:1;s:5:\"world\";}} consectetur adipiscing elit.';
        $this->assertTrue(Utils::containsSerializedDataRegex($stringWithSerializedDataRegex));

        $stringWithoutSerializedDataRegex = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $this->assertNotTrue(Utils::containsSerializedDataRegex($stringWithoutSerializedDataRegex));
    }

    /**
     * @unreleased
     *
     * @dataProvider serializedDataProvider
     */
    public function testIsSerialized($data, bool $expected)
    {
        if ($expected) {
            $this->assertTrue(Utils::isSerialized($data));
        } else {
            $this->assertFalse(Utils::isSerialized($data));
        }
    }

    /**
     * @unreleased
     *
     * @dataProvider serializedDataProvider
     */
    public function testSafeUnserialize($data, bool $expected)
    {
        $unserializedData = Utils::safeUnserialize($data);
        if ($expected) {
            $this->assertNotEquals($unserializedData, $data);
        } else {
            $this->assertEquals($unserializedData, $data);
        }
    }

    /**
     * @unreleased
     *
     * @dataProvider serializedDataProvider
     */
    public function testMaybeSafeUnserialize($data, bool $expected)
    {
        $unserializedData = Utils::maybeSafeUnserialize($data);
        if ($expected) {
            $this->assertNotEquals($unserializedData, $data);
        } else {
            $this->assertEquals($unserializedData, $data);
        }
    }

    /**
     * @unreleased
     */
    public function serializedDataProvider(): array
    {
        return [
            [serialize('bar'), true],
            ['\\' . serialize('backslash-bypass'), true],
            ['\\\\' . serialize('double-backslash-bypass'), true],
            [
                // String with serialized data hidden in the middle of the content
                'baz' => 'Lorem ipsum dolor sit amet, {a:2:{i:0;s:5:\"hello\";i:1;s:5:\"world\";}} consectetur adipiscing elit.',
                true,
            ],
            ['foo', false],
            [serialize('qux'), true],
            ['bar', false],
            ['foo bar', false],
        ];
    }
}
