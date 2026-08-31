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
     * Serialized objects must never be returned, not even as __PHP_Incomplete_Class instances,
     * because PHP writes the original class bytes back when they are serialized again —
     * which would re-arm the payload for the next unrestricted unserialize() call.
     *
     * @since 4.16.6
     * @since 4.16.7.2   Returns false instead of the raw serialized string.
     */
    public function testSafeUnserializeNeverReturnsObjects()
    {
        $objectPayload = serialize((object)['name' => 'James']);
        $this->assertFalse(Utils::safeUnserialize($objectPayload));
        $this->assertFalse(Utils::maybeSafeUnserialize($objectPayload));

        // Objects nested inside arrays are also neutralized.
        $nestedPayload = serialize(['name' => 'James', 'object' => (object)['name' => 'James']]);
        $this->assertFalse(Utils::safeUnserialize($nestedPayload));
        $this->assertFalse(Utils::maybeSafeUnserialize($nestedPayload));
    }

    /**
     * safeUnserialize must return false for object payloads to prevent
     * re-serialization and gadget chain exploitation.
     *
     * @since 4.16.7.2
     */
    public function testSafeUnserializeReturnsFalseForObjectPayloads()
    {
        // Simple object.
        $this->assertFalse(Utils::safeUnserialize(serialize((object)['test' => 'value'])));

        // Nested object in array.
        $this->assertFalse(Utils::safeUnserialize(serialize(['data' => (object)['key' => 'val']])));

        // Object with custom class.
        $this->assertFalse(Utils::safeUnserialize(serialize(new \stdClass())));

        // Re-serialization of false must not produce valid serialized string.
        $result = Utils::safeUnserialize(serialize((object)['evil' => 'payload']));
        $this->assertFalse($result);
    }

    /**
     * Legitimate serialized data (arrays, strings, numbers, booleans, null) must keep
     * being unserialized normally.
     *
     * @since 4.16.6
     */
    public function testSafeUnserializeStillUnserializesNonObjectData()
    {
        $this->assertSame(['hello', 'world'], Utils::safeUnserialize(serialize(['hello', 'world'])));
        $this->assertSame(['name' => 'James'], Utils::safeUnserialize(serialize(['name' => 'James'])));
        $this->assertSame('bar', Utils::safeUnserialize(serialize('bar')));
        $this->assertSame(42, Utils::safeUnserialize(serialize(42)));
        $this->assertSame(3.14, Utils::safeUnserialize(serialize(3.14)));
        $this->assertTrue(Utils::safeUnserialize(serialize(true)));
        $this->assertNull(Utils::safeUnserialize(serialize(null)));
    }

    /**
     * @since 4.16.6
     */
    public function testContainsPhpIncompleteClass()
    {
        $incompleteClass = unserialize(serialize((object)['name' => 'James']), ['allowed_classes' => false]);

        $this->assertTrue(Utils::containsPhpIncompleteClass($incompleteClass));
        $this->assertTrue(Utils::containsPhpIncompleteClass(['name' => 'James', 'object' => $incompleteClass]));
        $this->assertTrue(Utils::containsPhpIncompleteClass(['nested' => ['deep' => $incompleteClass]]));
        $this->assertFalse(Utils::containsPhpIncompleteClass(['hello', 'world']));
        $this->assertFalse(Utils::containsPhpIncompleteClass('bar'));
        $this->assertFalse(Utils::containsPhpIncompleteClass(false));
        $this->assertFalse(Utils::containsPhpIncompleteClass(null));
    }

    /**
     * @since 3.20.0 Test encoded strings and strings with special characters
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
            // Samples using multiple obfuscation techniques together
            [
                // Single URL-encoded for O😼:8:"stdClass":1:{s😼:4:"name";s😼:5:"James";}
                'O%F0%9F%98%BC%3A8%3A%22stdClass%22%3A1%3A%7Bs%F0%9F%98%BC%3A4%3A%22name%22%3Bs%F0%9F%98%BC%3A5%3A%22James%22%3B%7D',
                true,
            ],
            [
                // Double URL-encoded for O😼:8:"stdClass":1:{s😼:4:"name";s😼:5:"James";}
                'O%25F0%259F%2598%25BC%253A8%253A%2522stdClass%2522%253A1%253A%257Bs%25F0%259F%2598%25BC%253A4%253A%2522name%2522%253Bs%25F0%259F%2598%25BC%253A5%253A%2522James%2522%253B%257D',
                true,
            ],
        ];
    }
}
