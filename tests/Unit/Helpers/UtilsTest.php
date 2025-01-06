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
            // BYPASS WITH SIMPLE METHODS
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
            // BYPASS WITH ENCODING STRING METHOD #1 - URL-encoded
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
            // BYPASS WITH ENCODING STRING METHOD #2 - Base64
            [
                // Single encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                'Tzo4OiJzdGRDbGFzcyI6MTp7czo0OiJuYW1lIjtzOjU6IkphbWVzIjt9',
                true,
            ],
            [
                // Double encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                'VHp6MDpPOmp6I3N0ZENsYXNzIjoxOntzOjQ6Im5hbWUiO3M6NToiSmFtZXMiO31z',
                true,
            ],
            // BYPASS WITH ENCODING STRING METHOD #3 - Hex-encoded
            [
                // Single encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                '4f3a383a22737464436c617373223a313a7b733a343a226e616d65223b733a353a224a616d6573223b7d',
                true,
            ],
            [
                // Double encode for O:8:"stdClass":1:{s:4:"name";s:5:"James";}
                '346633613833613a323237333634343336643661373332223a313a376233343a313a3763363a373233333634353a343a66337a343a323233643634663a373236333a666537333a393a6666372e7a3a313b',
                true,
            ],
            // Real-world samples using multiple obfuscation techniques together
            [
                // Double URL-encoded for O😼:5:"TCPDF":2:{s😼:12:" * imagekeys";a😼:1:{i😼:0;s😼:34:"/tmp/../var/www/html/wp-config.php";}s😼:10:" * file_id";s😼:32:"202cb962ac59075b964b07152d234b70";}
                'O%25F0%259F%2598%25BC:5:%22TCPDF%22:2:{s%25F0%259F%2598%25BC:12:%22%00*%00imagekeys%22;a%25F0%259F%2598%25BC:1:{i%25F0%259F%2598%25BC:0;s%25F0%259F%2598%25BC:34:%22/tmp/../var/www/html/wp-config.php%22;}s%25F0%259F%2598%25BC:10:%22%00*%00file_id%22;s%25F0%259F%2598%25BC:32:%22202cb962ac59075b964b07152d234b70%22;}',
                true,
            ],
        ];
    }
}
