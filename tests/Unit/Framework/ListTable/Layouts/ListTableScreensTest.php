<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Layouts\ListTableScreens;
use Give\Tests\TestCase;

/**
 * The key list here must match the TypeScript `ListTableScreenKey` union, which has its own test.
 *
 * @since TBD
 */
class ListTableScreensTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testListsTheEightScreenKeys(): void
    {
        $this->assertSame(
            [
                'campaigns',
                'donation-forms',
                'campaign-forms',
                'donations',
                'donors',
                'subscriptions',
                'event-tickets',
                'subscription-donations',
            ],
            (new ListTableScreens())->keys()
        );
    }

    /**
     * @since TBD
     */
    public function testKnowsOnlyItsOwnKeys(): void
    {
        $screens = new ListTableScreens();

        $this->assertTrue($screens->has('donors'));
        $this->assertFalse($screens->has('settings'));
        $this->assertFalse($screens->has(''));
    }

    /**
     * @since TBD
     *
     * @dataProvider capabilityProvider
     *
     * @param string[] $expectedScreens
     */
    public function testMapsEachScreenToTheCapabilityOfTheAdminPageThatShowsIt(
        string $capability,
        array $expectedScreens
    ): void {
        $user = self::factory()->user->create_and_get(['role' => '']);
        $user->add_cap($capability);
        wp_set_current_user($user->ID);

        $this->assertSame($expectedScreens, (new ListTableScreens())->viewableByCurrentUser());
    }

    /**
     * @since TBD
     */
    public function capabilityProvider(): array
    {
        return [
            'view_give_forms' => [
                'view_give_forms',
                ['campaigns', 'donation-forms', 'campaign-forms', 'event-tickets'],
            ],
            'view_give_payments' => [
                'view_give_payments',
                ['donations', 'subscriptions', 'subscription-donations'],
            ],
            'view_give_reports' => ['view_give_reports', ['donors']],
        ];
    }

    /**
     * @since TBD
     */
    public function testListsOnlyTheScreensTheCurrentUserCanView(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'give_worker']));

        $this->assertSame(
            [
                'campaigns',
                'donation-forms',
                'campaign-forms',
                'donations',
                'subscriptions',
                'event-tickets',
                'subscription-donations',
            ],
            (new ListTableScreens())->viewableByCurrentUser()
        );
    }

    /**
     * @since TBD
     */
    public function testGivesASignedOutVisitorNoScreens(): void
    {
        wp_set_current_user(0);

        $screens = new ListTableScreens();

        $this->assertSame([], $screens->viewableByCurrentUser());
        $this->assertFalse($screens->currentUserCanView('donations'));
        $this->assertFalse($screens->currentUserCanView('settings'));
    }
}
