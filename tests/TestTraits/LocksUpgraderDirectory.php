<?php

namespace Give\Tests\TestTraits;

/**
 * Serializes tests that drive WP_Upgrader across parallel test workers.
 *
 * WP_Upgrader::unpack_package() empties WP_CONTENT_DIR/upgrade before it extracts a package, and
 * every paratest worker shares that directory. Holding this lock for the duration of a test keeps
 * one worker from deleting another worker's package mid-install.
 *
 * @since 4.17.0
 */
trait LocksUpgraderDirectory
{
    /**
     * @var resource|null
     */
    private $upgraderLock;

    /**
     * @since 4.17.0
     */
    protected function lockUpgraderDirectory(): void
    {
        $this->upgraderLock = fopen(WP_CONTENT_DIR . '/.upgrader.lock', 'c');
        flock($this->upgraderLock, LOCK_EX);
    }

    /**
     * @since 4.17.0
     */
    protected function unlockUpgraderDirectory(): void
    {
        if (!$this->upgraderLock) {
            return;
        }

        flock($this->upgraderLock, LOCK_UN);
        fclose($this->upgraderLock);
        $this->upgraderLock = null;
    }
}
