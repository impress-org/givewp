<?php

namespace Give\Tests\Unit\Framework\ListTable\FilterOptions;

use Give\Framework\ListTable\FilterOptions\FilterOptionSources;
use Give\Framework\ListTable\Layouts\ListTableScreens;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class FilterOptionSourcesTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testCampaignIsOfferedOnFourScreens(): void
    {
        $sources = new FilterOptionSources();

        foreach (['donation-forms', 'donations', 'donors', 'subscriptions'] as $screen) {
            $this->assertTrue($sources->isOfferedOn('campaign', $screen), $screen);
        }

        foreach (['campaigns', 'campaign-forms', 'event-tickets', 'subscription-donations'] as $screen) {
            $this->assertFalse($sources->isOfferedOn('campaign', $screen), $screen);
        }
    }

    /**
     * @since TBD
     */
    public function testFormAndDonorAreOfferedOnDonationsOnly(): void
    {
        $sources = new FilterOptionSources();

        foreach (['form', 'donor'] as $source) {
            foreach (give(ListTableScreens::class)->keys() as $screen) {
                $this->assertSame($screen === 'donations', $sources->isOfferedOn($source, $screen), "$source on $screen");
            }
        }
    }

    /**
     * @since TBD
     */
    public function testKnowsItsThreeSources(): void
    {
        $sources = new FilterOptionSources();

        foreach (['campaign', 'form', 'donor'] as $source) {
            $this->assertTrue($sources->has($source));
        }

        $this->assertFalse($sources->has('email'));
        $this->assertFalse($sources->isOfferedOn('email', 'donations'));
    }

    /**
     * @since TBD
     */
    public function testEveryScreenNamedExistsInListTableScreens(): void
    {
        $known = give(ListTableScreens::class)->keys();

        foreach (['campaign', 'form', 'donor'] as $source) {
            foreach ((new FilterOptionSources())->screensFor($source) as $screen) {
                $this->assertContains($screen, $known);
            }
        }
    }

    /**
     * @since TBD
     */
    public function testUnknownSourceErrorEscapesTheSourceName(): void
    {
        try {
            (new FilterOptionSources())->get('<b>x</b>');
            $this->fail('Expected an InvalidArgumentException.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Unknown filter option source: &lt;b&gt;x&lt;/b&gt;', $exception->getMessage());
        }
    }
}
