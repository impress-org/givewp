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
     * @unreleased Test encoded strings and strings with special characters
     * @since 3.19.3 Test all types of serialized data
     * @since 3.17.2
     */
    public function serializedDataProvider(): array
    {
        return [
            [serialize('bar'), true],
            ['foo', false],
            [serialize('qux'), true],
            ['bar', false],
            ['foo bar', false],
            // Strings with serialized data hidden in the middle of the content
            ['Lorem ipsum a:2:{i:0;s:5:"hello";i:1;i:42;} dolor sit amet', true], // array
            ['Lorem ipsum O:8:"stdClass":1:{s:4:"name";s:5:"James";} dolor sit amet', true], // object
            ['Lorem ipsum s:5:"hello"; dolor sit amet', true], // string
            ['Lorem ipsum i:42; dolor sit amet', true], // integer
            ['Lorem ipsum b:1; dolor sit amet', true], // boolean
            ['Lorem ipsum d:3.14; dolor sit amet', true], // float
            ['Lorem ipsum N; dolor sit amet', true], // NULL
            // Strings with special characters (e.g: emojis, spaces, control characters) that are not part of a predefined set of safe characters for serialized data structures (used to try to bypass the validations)
            [
                // emojis bypass sample
                'O😼:8:"stdClass":1:{s😼:4:"name";s😼:5:"James";}',
                true,
            ],
            [
                // spaces bypass sample
                'O :8:"stdClass":1:{s :4:"name";s :5:"James";}',
                true,
            ],
            // Bypass with simple methods
            [
                // backslash
                '\\' . serialize('backslash-bypass'),
                true,
            ],
            [
                // double-backslash
                '\\\\' . serialize('double-backslash-bypass'),
                true,
            ],
            // Bypass with encoding string method - URL-encoded
            [
                // Single encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                'O%3A8%3A%22stdClass%22%3A1%3A%7Bs%3A4%3A%22name%22%3Bs%3A5%3A%22James%22%3B%7D',
                true,
            ],
            [
                // Double encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                'O%253A8%253A%2522stdClass%2522%253A1%253A%257Bs%253A4%253A%2522name%2522%253Bs%253A5%253A%2522James%2522%253B%257D',
                true,
            ],
            // Sample using multiple obfuscation techniques together
            [
                // Double URL-encoded for O😼:5:"CLASS":2:{s😼:12:" * imagekeys";a😼:1:{i😼:0;s😼:31:"/server/path/file-to-delete.php";}s😼:10:" * file_id";s😼:32:"202cb962ac59075b964b07152d234b70";}
                'O%25F0%259F%2598%25BC%253A5%253A%2522CLASS%2522%253A2%253A%257Bs%25F0%259F%2598%25BC%253A12%253A%2522%2B%252A%2Bimagekeys%2522%253Ba%25F0%259F%2598%25BC%253A1%253A%257Bi%25F0%259F%2598%25BC%253A0%253Bs%25F0%259F%2598%25BC%253A31%253A%2522%252Fserver%252Fpath%252Ffile-to-delete.php%2522%253B%257Ds%25F0%259F%2598%25BC%253A10%253A%2522%2B%252A%2Bfile_id%2522%253Bs%25F0%259F%2598%25BC%253A32%253A%2522202cb962ac59075b964b07152d234b70%2522%253B%257D',
                true,
            ],
        ];
    }
}
