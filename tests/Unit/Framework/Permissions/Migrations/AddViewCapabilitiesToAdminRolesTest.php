<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Permissions\Migrations;

use Give\Framework\Permissions\Migrations\AddViewCapabilitiesToAdminRoles;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @covers \Give\Framework\Permissions\Migrations\AddViewCapabilitiesToAdminRoles
 */
final class AddViewCapabilitiesToAdminRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToGiveManagerRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_manager doesn't have view_give_forms initially
        $wp_roles->remove_cap('give_manager', 'view_give_forms');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Assert: view_give_forms should be added to the role
        $this->assertTrue(
            $wp_roles->roles['give_manager']['capabilities']['view_give_forms'] ?? false,
            'give_manager should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGivePaymentsToGiveManagerRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_manager doesn't have view_give_payments initially
        $wp_roles->remove_cap('give_manager', 'view_give_payments');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Assert: view_give_payments should be added to the role
        $this->assertTrue(
            $wp_roles->roles['give_manager']['capabilities']['view_give_payments'] ?? false,
            'give_manager should have view_give_payments after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToAdministratorRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure administrator doesn't have view_give_forms initially
        $wp_roles->remove_cap('administrator', 'view_give_forms');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Assert: view_give_forms should be added to the role
        $this->assertTrue(
            $wp_roles->roles['administrator']['capabilities']['view_give_forms'] ?? false,
            'administrator should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGivePaymentsToAdministratorRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure administrator doesn't have view_give_payments initially
        $wp_roles->remove_cap('administrator', 'view_give_payments');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Assert: view_give_payments should be added to the role
        $this->assertTrue(
            $wp_roles->roles['administrator']['capabilities']['view_give_payments'] ?? false,
            'administrator should have view_give_payments after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToGiveManagerUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_forms from the role first
        $wp_roles->remove_cap('give_manager', 'view_give_forms');

        // Create a give_manager user
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_manager');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_forms should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_forms'),
            'give_manager user should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGivePaymentsToGiveManagerUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_payments from the role first
        $wp_roles->remove_cap('give_manager', 'view_give_payments');

        // Create a give_manager user
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_manager');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_payments should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_payments'),
            'give_manager user should have view_give_payments after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToAdministratorUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_forms from the role first
        $wp_roles->remove_cap('administrator', 'view_give_forms');

        // Create an administrator user
        $user = self::factory()->user->create_and_get();
        $user->set_role('administrator');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_forms should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_forms'),
            'administrator user should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGivePaymentsToAdministratorUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_payments from the role first
        $wp_roles->remove_cap('administrator', 'view_give_payments');

        // Create an administrator user
        $user = self::factory()->user->create_and_get();
        $user->set_role('administrator');

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_payments should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_payments'),
            'administrator user should have view_give_payments after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationDoesNotAffectOtherRoles(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_worker and give_accountant don't have these caps
        $wp_roles->remove_cap('give_worker', 'view_give_forms');
        $wp_roles->remove_cap('give_worker', 'view_give_payments');
        $wp_roles->remove_cap('give_accountant', 'view_give_forms');
        $wp_roles->remove_cap('give_accountant', 'view_give_payments');

        // Store original state
        $workerHadViewForms = $wp_roles->roles['give_worker']['capabilities']['view_give_forms'] ?? false;
        $workerHadViewPayments = $wp_roles->roles['give_worker']['capabilities']['view_give_payments'] ?? false;
        $accountantHadViewForms = $wp_roles->roles['give_accountant']['capabilities']['view_give_forms'] ?? false;
        $accountantHadViewPayments = $wp_roles->roles['give_accountant']['capabilities']['view_give_payments'] ?? false;

        // Act: Run the migration
        (new AddViewCapabilitiesToAdminRoles())->run();

        // Assert: Other roles should not be affected by this migration
        $this->assertEquals(
            $workerHadViewForms,
            $wp_roles->roles['give_worker']['capabilities']['view_give_forms'] ?? false,
            'give_worker view_give_forms should not be affected by this migration'
        );

        $this->assertEquals(
            $workerHadViewPayments,
            $wp_roles->roles['give_worker']['capabilities']['view_give_payments'] ?? false,
            'give_worker view_give_payments should not be affected by this migration'
        );

        $this->assertEquals(
            $accountantHadViewForms,
            $wp_roles->roles['give_accountant']['capabilities']['view_give_forms'] ?? false,
            'give_accountant view_give_forms should not be affected by this migration'
        );

        $this->assertEquals(
            $accountantHadViewPayments,
            $wp_roles->roles['give_accountant']['capabilities']['view_give_payments'] ?? false,
            'give_accountant view_give_payments should not be affected by this migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationHasCorrectId(): void
    {
        $this->assertSame('add_view_capabilities_to_admin_roles', AddViewCapabilitiesToAdminRoles::id());
    }

    /**
     * @unreleased
     */
    public function testMigrationHasCorrectTitle(): void
    {
        $this->assertSame(
            'Add view capabilities to GiveWP Manager and Administrator roles',
            AddViewCapabilitiesToAdminRoles::title()
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationHasValidTimestamp(): void
    {
        $timestamp = AddViewCapabilitiesToAdminRoles::timestamp();

        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);
    }
}
