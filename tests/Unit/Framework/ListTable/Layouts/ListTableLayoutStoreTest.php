<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Layouts\ListTableLayoutStore;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class ListTableLayoutStoreTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testReturnsNullWhenNothingIsStored(): void
    {
        $userId = self::factory()->user->create();

        $this->assertNull((new ListTableLayoutStore())->get($userId, 'donations'));
    }

    /**
     * @since TBD
     */
    public function testSavesAndReadsOneRecordPerScreen(): void
    {
        $userId = self::factory()->user->create();
        $store = new ListTableLayoutStore();

        $store->save($userId, 'donations', ['version' => 1, 'density' => 'compact']);
        $store->save($userId, 'donors', ['version' => 1, 'perPage' => 50]);

        $this->assertSame(['version' => 1, 'density' => 'compact'], $store->get($userId, 'donations'));
        $this->assertSame(['version' => 1, 'perPage' => 50], $store->get($userId, 'donors'));
    }

    /**
     * @since TBD
     */
    public function testStoresTheRecordUnderASitePrefixedOptionKey(): void
    {
        global $wpdb;

        $userId = self::factory()->user->create();

        (new ListTableLayoutStore())->save($userId, 'donations', ['version' => 1]);

        $this->assertSame(
            ['version' => 1],
            get_user_meta($userId, $wpdb->get_blog_prefix() . 'give_list_table_layout_donations', true)
        );
        $this->assertSame('', get_user_meta($userId, 'give_list_table_layout_donations', true));
    }

    /**
     * @since TBD
     */
    public function testUsesTheCurrentSitePrefixOnMultisite(): void
    {
        if (!is_multisite()) {
            $this->markTestSkipped('Runs only when the suite is multisite. Checked by hand otherwise.');
        }

        global $wpdb;

        $userId = self::factory()->user->create();
        $siteId = self::factory()->blog->create();
        $store = new ListTableLayoutStore();

        switch_to_blog($siteId);
        $store->save($userId, 'donations', ['version' => 1, 'density' => 'compact']);
        $prefix = $wpdb->get_blog_prefix();
        restore_current_blog();

        $this->assertSame(
            ['version' => 1, 'density' => 'compact'],
            get_user_meta($userId, $prefix . 'give_list_table_layout_donations', true)
        );
        $this->assertNull($store->get($userId, 'donations'));
    }

    /**
     * @since TBD
     */
    public function testDeletesTheRecord(): void
    {
        $userId = self::factory()->user->create();
        $store = new ListTableLayoutStore();

        $store->save($userId, 'donations', ['version' => 1]);
        $store->delete($userId, 'donations');

        $this->assertNull($store->get($userId, 'donations'));
    }

    /**
     * @since TBD
     */
    public function testIgnoresAStoredValueThatIsNotAnArray(): void
    {
        $userId = self::factory()->user->create();

        update_user_option($userId, 'give_list_table_layout_donations', 'compact');

        $this->assertNull((new ListTableLayoutStore())->get($userId, 'donations'));
    }

    /**
     * @since TBD
     */
    public function testAllForReturnsOnlyTheRequestedScreensThatHaveARecord(): void
    {
        $userId = self::factory()->user->create();
        $store = new ListTableLayoutStore();

        $store->save($userId, 'donations', ['version' => 1, 'density' => 'compact']);
        $store->save($userId, 'donors', ['version' => 1, 'perPage' => 50]);

        $this->assertSame(
            ['donations' => ['version' => 1, 'density' => 'compact']],
            $store->allFor($userId, ['donations', 'campaigns'])
        );
    }

    /**
     * @since TBD
     */
    public function testKeepsEachUsersRecordSeparate(): void
    {
        $userA = self::factory()->user->create();
        $userB = self::factory()->user->create();
        $store = new ListTableLayoutStore();

        $store->save($userA, 'donations', ['version' => 1, 'density' => 'compact']);

        $this->assertNull($store->get($userB, 'donations'));
        $this->assertSame([], $store->allFor($userB, ['donations']));
    }
}
