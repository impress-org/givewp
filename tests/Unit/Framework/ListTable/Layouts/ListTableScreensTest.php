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
     */
    public function testMapsEachScreenToTheCapabilityOfTheAdminPageThatShowsIt(): void
    {
        $screens = new ListTableScreens();

        $this->assertSame('view_give_forms', $screens->capability('campaigns'));
        $this->assertSame('view_give_forms', $screens->capability('donation-forms'));
        $this->assertSame('view_give_forms', $screens->capability('campaign-forms'));
        $this->assertSame('view_give_payments', $screens->capability('donations'));
        $this->assertSame('view_give_reports', $screens->capability('donors'));
        $this->assertSame('view_give_payments', $screens->capability('subscriptions'));
        $this->assertSame('view_give_forms', $screens->capability('event-tickets'));
        $this->assertSame('view_give_payments', $screens->capability('subscription-donations'));
        $this->assertSame('', $screens->capability('settings'));
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
