<?php

namespace Give\Tests\Unit\Framework\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Framework\Migrations\MigrationsRunner;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Upgrader;

/**
 * @since TBD
 */
class TestMigrationsRunner extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists('WP_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
    }

    /**
     * @since TBD
     */
    public function testSkipsWhileAnotherRequestHoldsTheLock()
    {
        $register = new MigrationsRegister();
        $register->addMigration(LockedOutMigration::class);
        $runner = new MigrationsRunner($register, give(MigrationLogFactory::class), give(MigrationLogRepository::class));

        WP_Upgrader::create_lock(MigrationsRunner::LOCK_NAME);
        $runner->run();
        $this->assertSame(0, LockedOutMigration::$runs);

        WP_Upgrader::release_lock(MigrationsRunner::LOCK_NAME);
        $runner->run();
        $this->assertSame(1, LockedOutMigration::$runs);
        $this->assertFalse(get_option(MigrationsRunner::LOCK_NAME . '.lock'));
    }
}

class LockedOutMigration extends Migration
{
    public static $runs = 0;

    public static function id()
    {
        return 'locked_out_migration';
    }

    public static function timestamp()
    {
        return strtotime('2026-09-19');
    }

    public function run()
    {
        self::$runs++;
    }
}
