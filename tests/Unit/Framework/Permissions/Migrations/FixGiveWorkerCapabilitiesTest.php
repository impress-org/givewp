<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Permissions\Migrations;

use Give\Framework\Permissions\Migrations\FixGiveWorkerCapabilities;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.14.0
 *
 * @covers \Give\Framework\Permissions\Migrations\FixGiveWorkerCapabilities
 */
final class FixGiveWorkerCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.14.0
     */
    public function testMigrationRemovesEditGivePaymentsFromGiveWorkerRole(): void
    {
        global $wp_roles;

        // Arrange: Simulate the old (incorrect) state where give_worker has edit_give_payments
        $wp_roles->add_cap('give_worker', 'edit_give_payments');

        $this->assertTrue(
            $wp_roles->roles['give_worker']['capabilities']['edit_give_payments'] ?? false,
            'Precondition: give_worker should have edit_give_payments before migration'
        );

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Assert: edit_give_payments should be removed from the role
        $this->assertArrayNotHasKey(
            'edit_give_payments',
            $wp_roles->roles['give_worker']['capabilities'],
            'give_worker should NOT have edit_give_payments after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationAddsViewGivePaymentsToGiveWorkerRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_worker doesn't have view_give_payments initially
        $wp_roles->remove_cap('give_worker', 'view_give_payments');

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Assert: view_give_payments should be added to the role
        $this->assertTrue(
            $wp_roles->roles['give_worker']['capabilities']['view_give_payments'] ?? false,
            'give_worker should have view_give_payments after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationAddsViewGiveFormsToGiveWorkerRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_worker doesn't have view_give_forms initially
        $wp_roles->remove_cap('give_worker', 'view_give_forms');

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Assert: view_give_forms should be added to the role
        $this->assertTrue(
            $wp_roles->roles['give_worker']['capabilities']['view_give_forms'] ?? false,
            'give_worker should have view_give_forms after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationRemovesEditGivePaymentsFromIndividualUser(): void
    {
        // Arrange: Create a give_worker user with individually granted edit_give_payments
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_worker');

        // Manually grant the capability to the user (simulating individual override)
        $user->add_cap('edit_give_payments');

        $this->assertTrue(
            $user->has_cap('edit_give_payments'),
            'Precondition: user should have edit_give_payments before migration'
        );

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: The user's individual caps should not include edit_give_payments
        // Note: We check the user's allcaps directly to avoid role inheritance checks
        $this->assertArrayNotHasKey(
            'edit_give_payments',
            $user->caps,
            'give_worker user individual caps should NOT include edit_give_payments after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationAddsViewGivePaymentsToIndividualUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_payments from the role first
        $wp_roles->remove_cap('give_worker', 'view_give_payments');

        // Create a give_worker user
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_worker');

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_payments should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_payments'),
            'give_worker user should have view_give_payments after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationAddsViewGiveFormsToIndividualUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_forms from the role first
        $wp_roles->remove_cap('give_worker', 'view_give_forms');

        // Create a give_worker user
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_worker');

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_forms should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_forms'),
            'give_worker user should have view_give_forms after migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationDoesNotAffectOtherRoles(): void
    {
        // Arrange: Create users with other roles
        $accountant = self::factory()->user->create_and_get();
        $accountant->set_role('give_accountant');

        $manager = self::factory()->user->create_and_get();
        $manager->set_role('give_manager');

        // Store original capabilities
        $accountantHadEditPayments = $accountant->has_cap('edit_give_payments');
        $managerHadEditPayments = $manager->has_cap('edit_give_payments');

        // Act: Run the migration
        (new FixGiveWorkerCapabilities())->run();

        // Clear cache and refresh user data
        clean_user_cache($accountant->ID);
        clean_user_cache($manager->ID);
        $accountant = new \WP_User($accountant->ID);
        $manager = new \WP_User($manager->ID);

        // Assert: Other roles should not be affected
        $this->assertEquals(
            $accountantHadEditPayments,
            $accountant->has_cap('edit_give_payments'),
            'give_accountant capabilities should not be affected by migration'
        );

        $this->assertEquals(
            $managerHadEditPayments,
            $manager->has_cap('edit_give_payments'),
            'give_manager capabilities should not be affected by migration'
        );
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationHasCorrectId(): void
    {
        $this->assertSame('fix_give_worker_capabilities', FixGiveWorkerCapabilities::id());
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationHasCorrectTitle(): void
    {
        $this->assertSame('Fix GiveWP Worker role capabilities', FixGiveWorkerCapabilities::title());
    }

    /**
     * @since 4.14.0
     */
    public function testMigrationHasValidTimestamp(): void
    {
        $timestamp = FixGiveWorkerCapabilities::timestamp();

        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);
    }
}

