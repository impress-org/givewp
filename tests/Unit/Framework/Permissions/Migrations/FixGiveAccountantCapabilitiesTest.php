<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Permissions\Migrations;

use Give\Framework\Permissions\Migrations\FixGiveAccountantCapabilities;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @covers \Give\Framework\Permissions\Migrations\FixGiveAccountantCapabilities
 */
final class FixGiveAccountantCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testMigrationRemovesEditGiveFormsFromGiveAccountantRole(): void
    {
        global $wp_roles;

        // Arrange: Simulate the old (incorrect) state where give_accountant has edit_give_forms
        $wp_roles->add_cap('give_accountant', 'edit_give_forms');

        $this->assertTrue(
            $wp_roles->roles['give_accountant']['capabilities']['edit_give_forms'] ?? false,
            'Precondition: give_accountant should have edit_give_forms before migration'
        );

        // Act: Run the migration
        (new FixGiveAccountantCapabilities())->run();

        // Assert: edit_give_forms should be removed from the role
        $this->assertArrayNotHasKey(
            'edit_give_forms',
            $wp_roles->roles['give_accountant']['capabilities'],
            'give_accountant should NOT have edit_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToGiveAccountantRole(): void
    {
        global $wp_roles;

        // Arrange: Ensure give_accountant doesn't have view_give_forms initially
        $wp_roles->remove_cap('give_accountant', 'view_give_forms');

        // Act: Run the migration
        (new FixGiveAccountantCapabilities())->run();

        // Assert: view_give_forms should be added to the role
        $this->assertTrue(
            $wp_roles->roles['give_accountant']['capabilities']['view_give_forms'] ?? false,
            'give_accountant should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationRemovesEditGiveFormsFromIndividualUser(): void
    {
        // Arrange: Create a give_accountant user with individually granted edit_give_forms
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_accountant');

        // Manually grant the capability to the user (simulating individual override)
        $user->add_cap('edit_give_forms');

        $this->assertTrue(
            $user->has_cap('edit_give_forms'),
            'Precondition: user should have edit_give_forms before migration'
        );

        // Act: Run the migration
        (new FixGiveAccountantCapabilities())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: The user's individual caps should not include edit_give_forms
        // Note: We check the user's allcaps directly to avoid role inheritance checks
        $this->assertArrayNotHasKey(
            'edit_give_forms',
            $user->caps,
            'give_accountant user individual caps should NOT include edit_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationAddsViewGiveFormsToIndividualUser(): void
    {
        global $wp_roles;

        // Arrange: Remove view_give_forms from the role first
        $wp_roles->remove_cap('give_accountant', 'view_give_forms');

        // Create a give_accountant user
        $user = self::factory()->user->create_and_get();
        $user->set_role('give_accountant');

        // Act: Run the migration
        (new FixGiveAccountantCapabilities())->run();

        // Clear WordPress user cache and refresh user data
        clean_user_cache($user->ID);
        $user = new \WP_User($user->ID);

        // Assert: view_give_forms should be present (via role, which was fixed by migration)
        $this->assertTrue(
            $user->has_cap('view_give_forms'),
            'give_accountant user should have view_give_forms after migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationDoesNotAffectOtherRoles(): void
    {
        // Arrange: Create users with other roles
        $worker = self::factory()->user->create_and_get();
        $worker->set_role('give_worker');

        $manager = self::factory()->user->create_and_get();
        $manager->set_role('give_manager');

        // Store original capabilities
        $workerHadEditForms = $worker->has_cap('edit_give_forms');
        $managerHadEditForms = $manager->has_cap('edit_give_forms');

        // Act: Run the migration
        (new FixGiveAccountantCapabilities())->run();

        // Clear cache and refresh user data
        clean_user_cache($worker->ID);
        clean_user_cache($manager->ID);
        $worker = new \WP_User($worker->ID);
        $manager = new \WP_User($manager->ID);

        // Assert: Other roles should not be affected
        $this->assertEquals(
            $workerHadEditForms,
            $worker->has_cap('edit_give_forms'),
            'give_worker capabilities should not be affected by migration'
        );

        $this->assertEquals(
            $managerHadEditForms,
            $manager->has_cap('edit_give_forms'),
            'give_manager capabilities should not be affected by migration'
        );
    }

    /**
     * @unreleased
     */
    public function testMigrationHasCorrectId(): void
    {
        $this->assertSame('fix_give_accountant_capabilities', FixGiveAccountantCapabilities::id());
    }

    /**
     * @unreleased
     */
    public function testMigrationHasCorrectTitle(): void
    {
        $this->assertSame('Fix GiveWP Accountant role capabilities', FixGiveAccountantCapabilities::title());
    }

    /**
     * @unreleased
     */
    public function testMigrationHasValidTimestamp(): void
    {
        $timestamp = FixGiveAccountantCapabilities::timestamp();

        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);
    }
}
