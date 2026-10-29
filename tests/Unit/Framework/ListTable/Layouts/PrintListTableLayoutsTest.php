<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Actions\RegisterListTableAssets;
use Give\Framework\ListTable\Layouts\ListTableLayoutStore;
use Give\Framework\ListTable\Layouts\PrintListTableLayouts;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class PrintListTableLayoutsTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        give(RegisterListTableAssets::class)();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        wp_deregister_script(RegisterListTableAssets::HANDLE);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testPrintsTheRoutePathAndTheUsersOwnLayoutsBeforeTheSharedHandle(): void
    {
        $userId = $this->signIn('administrator');
        $other = self::factory()->user->create(['role' => 'administrator']);
        $store = give(ListTableLayoutStore::class);

        $store->save($userId, 'donations', ['version' => 1, 'visibility' => ['form' => false], 'perPage' => 50]);
        $store->save($other, 'donors', ['version' => 1, 'density' => 'compact']);

        give(PrintListTableLayouts::class)();

        $this->assertSame(
            [
                'path' => '/givewp/v3/list-table-layouts',
                'layouts' => [
                    'donations' => ['version' => 1, 'visibility' => ['form' => false], 'perPage' => 50],
                ],
            ],
            $this->printedData()
        );
        $this->assertSame([], wp_scripts()->get_data(RegisterListTableAssets::HANDLE, 'after') ?: []);
    }

    /**
     * @since TBD
     */
    public function testPrintsOnlyTheScreensTheUserCanView(): void
    {
        $userId = $this->signIn('give_worker');
        $store = give(ListTableLayoutStore::class);

        $store->save($userId, 'donations', ['version' => 1, 'density' => 'compact']);
        $store->save($userId, 'donors', ['version' => 1, 'density' => 'compact']);

        give(PrintListTableLayouts::class)();

        $this->assertSame(['donations'], array_keys($this->printedData()['layouts']));
    }

    /**
     * @since TBD
     */
    public function testPrintsAnEmptyObjectWhenThereAreNoLayouts(): void
    {
        $this->signIn('administrator');

        give(PrintListTableLayouts::class)();

        $this->assertStringContainsString('"layouts":{}', $this->inlineScript());
    }

    /**
     * @since TBD
     */
    public function testLeavesOutAStoredRecordThatIsNoLongerValid(): void
    {
        $userId = $this->signIn('administrator');

        update_user_option($userId, 'give_list_table_layout_donations', [
            'version' => 1,
            'visibility' => ['</script><script>alert(1)</script>' => false],
        ]);
        update_user_option($userId, 'give_list_table_layout_donors', [
            'version' => 1,
            'search' => 'jane@example.com',
        ]);

        give(PrintListTableLayouts::class)();

        $script = $this->inlineScript();

        $this->assertStringNotContainsString('<', $script);
        $this->assertStringNotContainsString('jane', $script);
        $this->assertSame([], $this->printedData()['layouts']);
    }

    /**
     * @since TBD
     */
    public function testPrintsNothingForASignedOutVisitor(): void
    {
        wp_set_current_user(0);

        give(PrintListTableLayouts::class)();

        $this->assertSame('', $this->inlineScript());
    }

    /**
     * @since TBD
     */
    public function testPrintsNothingWhenTheSharedHandleIsNotRegistered(): void
    {
        $this->signIn('administrator');
        wp_deregister_script(RegisterListTableAssets::HANDLE);

        give(PrintListTableLayouts::class)();

        $this->assertFalse(wp_scripts()->get_data(RegisterListTableAssets::HANDLE, 'before'));
    }

    /**
     * @since TBD
     */
    private function signIn(string $role): int
    {
        $userId = self::factory()->user->create(['role' => $role]);
        wp_set_current_user($userId);

        return $userId;
    }

    /**
     * @since TBD
     */
    private function inlineScript(): string
    {
        $before = wp_scripts()->get_data(RegisterListTableAssets::HANDLE, 'before');

        return is_array($before) ? implode("\n", array_filter($before)) : '';
    }

    /**
     * @since TBD
     */
    private function printedData(): array
    {
        $script = $this->inlineScript();
        $prefix = 'window.GiveListTableLayouts = ';

        $this->assertStringStartsWith($prefix, $script);
        $this->assertStringEndsWith(';', $script);

        return json_decode(substr($script, strlen($prefix), -1), true);
    }
}
