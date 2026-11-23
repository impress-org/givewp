<?php

namespace Give\Tests\Unit\Framework\WordPressLibraries;

use Give\Tests\TestCase;
use Give_Background_Updater;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @since TBD
 */
final class BackgroundProcessKeyTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/class-give-background-updater.php';
    }

    /**
     * The identifier names the stored queue rows and the cron event, so it must not change.
     *
     * @since TBD
     */
    public function testUpdaterIdentifierIsUnchanged(): void
    {
        $updater = new Give_Background_Updater();
        $identifier = new ReflectionProperty($updater, 'identifier');
        $identifier->setAccessible(true);

        $this->assertSame('wp_give_db_updater', $identifier->getValue($updater));
    }

    /**
     * @since TBD
     */
    public function testQueueKeysHaveTheExpectedShapeAndLength(): void
    {
        $updater = new Give_Background_Updater();
        $generateKey = new ReflectionMethod($updater, 'generate_key');
        $generateKey->setAccessible(true);

        $first = $generateKey->invoke($updater);
        $second = $generateKey->invoke($updater);

        $this->assertMatchesRegularExpression('/^wp_give_db_updater_batch_[0-9a-f]{32}$/', $first);
        $this->assertNotSame($first, $second);
        $this->assertSame(30, strlen($generateKey->invoke($updater, 30)));
    }
}
