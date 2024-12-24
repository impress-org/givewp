<?php

namespace Give\Tests\Unit\Helpers;

use Give\Helpers\Utils;
use Give\Tests\TestCase;

/**
 * @since 3.17.2
 */
class UtilsTest extends TestCase
{
    /**
     * @since 3.17.2
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
     * @since 3.17.2
     */
    public function testContainsSerializedDataRegex()
    {
        $stringWithSerializedDataRegex = 'Lorem ipsum dolor sit amet, {a:2:{i:0;s:5:\"hello\";i:1;s:5:\"world\";}} consectetur adipiscing elit.';
        $this->assertTrue(Utils::containsSerializedDataRegex($stringWithSerializedDataRegex));

        $stringWithoutSerializedDataRegex = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $this->assertNotTrue(Utils::containsSerializedDataRegex($stringWithoutSerializedDataRegex));
    }

    /**
     * @since 3.17.2
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
     * @since 3.17.2
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
     * @since 3.17.2
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
     * @since 3.17.2
     */
    public function serializedDataProvider(): array
    {
        return [
            [serialize('bar'), true],
            ['\\' . serialize('backslash-bypass'), true],
            ['\\\\' . serialize('double-backslash-bypass'), true],
            ['foo', false],
            [serialize('qux'), true],
            ['bar', false],
            ['foo bar', false],
            // String with serialized data hidden in the middle of the content
            ['Lorem ipsum a:2:{i:0;s:5:"hello";i:1;i:42;} dolor sit amet', true], // array
            ['Lorem ipsum O:8:"stdClass":1:{s:4:"name";s:5:"James";} dolor sit amet', true], // object
            ['Lorem ipsum s:5:"hello"; dolor sit amet', true], // string
            ['Lorem ipsum i:42; dolor sit amet', true], // integer
            ['Lorem ipsum b:1; dolor sit amet', true], // boolean
            ['Lorem ipsum d:3.14; dolor sit amet', true], // float
            ['Lorem ipsum N; dolor sit amet', true], // NULL
        ];
    }
}
