<?php

namespace Give\Tests\Unit\Framework\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Controllers\ManualMigration;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class TestManualMigration extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        ManualRunMigration::$runs = 0;
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
    }

    public function tearDown(): void
    {
        unset($_GET['give-run-migration'], $_GET['give-clear-update'], $_GET['_wpnonce']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDoesNotRunMigrationWithoutNonce()
    {
        $_GET['give-run-migration'] = ManualRunMigration::id();

        $this->makeController()->__invoke();

        $this->assertSame(0, ManualRunMigration::$runs);
    }

    /**
     * @since TBD
     */
    public function testDoesNotRunMigrationWithInvalidNonce()
    {
        $_GET['give-run-migration'] = ManualRunMigration::id();
        $_GET['_wpnonce'] = 'invalid';

        $this->makeController()->__invoke();

        $this->assertSame(0, ManualRunMigration::$runs);
    }

    /**
     * @since TBD
     */
    public function testClearsMigrationWithValidNonce()
    {
        update_option('give_completed_upgrades', [ManualRunMigration::id()]);
        $_GET['give-clear-update'] = ManualRunMigration::id();
        $_GET['_wpnonce'] = wp_create_nonce('give_manual_migration');

        $this->makeController()->__invoke();

        $this->assertSame([], get_option('give_completed_upgrades'));
    }

    /**
     * @since TBD
     */
    public function testDoesNotClearMigrationWithoutNonce()
    {
        update_option('give_completed_upgrades', [ManualRunMigration::id()]);
        $_GET['give-clear-update'] = ManualRunMigration::id();

        $this->makeController()->__invoke();

        $this->assertSame([ManualRunMigration::id()], get_option('give_completed_upgrades'));
    }

    /**
     * @since TBD
     */
    private function makeController(): ManualMigration
    {
        $register = new MigrationsRegister();
        $register->addMigration(ManualRunMigration::class);

        return new ManualMigration($register);
    }
}

class ManualRunMigration extends Migration
{
    public static $runs = 0;

    public static function id()
    {
        return 'manual_run_migration';
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
