<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\RenderFormSkeleton;
use Give\Tests\TestCase;

/**
 * The markup is what the block and the form view print and what the embed stylesheets target, so
 * the class names and the per-design structure are pinned here.
 *
 * @since 4.17.0
 */
class RenderFormSkeletonTest extends TestCase
{
    /**
     * @since 4.17.0
     */
    public function testClassicRendersHeaderEverySectionAndButton()
    {
        $html = (new RenderFormSkeleton())($this->data([
            'design' => 'classic',
            'sections' => [['givewp/donation-amount'], ['givewp/donor-name', 'givewp/email']],
        ]));

        $this->assertStringStartsWith('<div class="givewp-embed-skeleton givewp-embed-skeleton--classic" aria-hidden="true">', $html);
        $this->assertSame(1, substr_count($html, 'givewp-embed-skeleton__header'));
        $this->assertSame(2, substr_count($html, 'givewp-embed-skeleton__section-header'), 'one heading per section');
        $this->assertSame(1, substr_count($html, 'givewp-embed-skeleton__field--amount'));
        $this->assertSame(2, substr_count($html, 'givewp-embed-skeleton__field--field"'));
        $this->assertSame(1, substr_count($html, 'givewp-embed-skeleton__button'));
        $this->assertStringNotContainsString('givewp-embed-skeleton__step', $html);
    }

    /**
     * @since 4.17.0
     */
    public function testMultiStepShowsOnlyTheHeaderStepWhenTheFormHasAHeader()
    {
        $html = (new RenderFormSkeleton())($this->data(['design' => 'multi-step']));

        $this->assertStringContainsString('givewp-embed-skeleton--multi-step', $html);
        $this->assertStringContainsString('givewp-embed-skeleton__step-header', $html);
        $this->assertStringContainsString('givewp-embed-skeleton__header', $html);
        $this->assertStringNotContainsString('givewp-embed-skeleton__field', $html);
        $this->assertStringContainsString('givewp-embed-skeleton__secure', $html);
    }

    /**
     * @since 4.17.0
     */
    public function testMultiStepShowsTheFirstSectionWhenThereIsNoHeader()
    {
        $html = (new RenderFormSkeleton())($this->data([
            'design' => 'multi-step',
            'header' => false,
            'sections' => [['givewp/donation-amount'], ['givewp/email']],
        ]));

        $this->assertStringNotContainsString('givewp-embed-skeleton__header', $html);
        $this->assertSame(1, substr_count($html, 'givewp-embed-skeleton__field--amount'));
        $this->assertStringNotContainsString('givewp-embed-skeleton__field--field', $html);
    }

    /**
     * @since 4.17.0
     */
    public function testTwoPanelPutsTheHeaderBesideTheFirstStep()
    {
        $html = (new RenderFormSkeleton())($this->data([
            'design' => 'two-panel-steps',
            'sections' => [['givewp/donation-amount'], ['givewp/email']],
        ]));

        $this->assertStringContainsString('givewp-embed-skeleton--two-panel', $html);
        $this->assertStringContainsString('givewp-embed-skeleton__header', $html);
        $this->assertStringContainsString('givewp-embed-skeleton__step', $html);
        $this->assertSame(1, substr_count($html, 'givewp-embed-skeleton__field--amount'));
        $this->assertStringNotContainsString('givewp-embed-skeleton__field--field', $html);
    }

    /**
     * @since 4.17.0
     */
    public function testHeaderPartsFollowTheImageAndGoalFlags()
    {
        $renderer = new RenderFormSkeleton();

        $plain = $renderer($this->data(['image' => false, 'goal' => false]));
        $this->assertStringNotContainsString('givewp-embed-skeleton__image', $plain);
        $this->assertStringNotContainsString('givewp-embed-skeleton__goal', $plain);

        $full = $renderer($this->data(['image' => true, 'goal' => true]));
        $this->assertStringContainsString('givewp-embed-skeleton__image', $full);
        $this->assertStringContainsString('givewp-embed-skeleton__goal', $full);
    }

    /**
     * @since 4.17.0
     */
    public function testGatewaysDrawOneRowPerGatewayPlusTheOpenPanel()
    {
        $html = (new RenderFormSkeleton())($this->data([
            'sections' => [['givewp/payment-gateways']],
            'gateways' => 3,
        ]));

        $field = substr($html, strpos($html, 'givewp-embed-skeleton__field--gateways'));
        $field = substr($field, 0, strpos($field, '</div>'));

        $this->assertSame(3, substr_count($field, 'givewp-embed-skeleton__input'));
        $this->assertSame(1, substr_count($field, 'givewp-embed-skeleton__panel'));
    }

    /**
     * @since 4.17.0
     */
    public function testUnknownDesignRendersNothing()
    {
        $this->assertSame('', (new RenderFormSkeleton())($this->data(['design' => 'addon-design'])));
        $this->assertSame('', (new RenderFormSkeleton())([]));
    }

    /**
     * The markup is injected with innerHTML on host pages, so nothing from the data may reach it
     * as text. The design id is checked against a fixed list, block names are lookup keys only,
     * and the remaining values are booleans and a count.
     *
     * @since 4.17.0
     */
    public function testNoDataValueReachesTheMarkup()
    {
        $hostile = '"><img src=x onerror=alert(1)>';
        $renderer = new RenderFormSkeleton();

        $this->assertSame('', $renderer($this->data(['design' => 'classic' . $hostile])));

        $html = $renderer($this->data([
            'sections' => [[$hostile, 'givewp/payment-gateways' . $hostile]],
            'gateways' => $hostile,
        ]));

        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertSame(2, substr_count($html, 'givewp-embed-skeleton__field--field"'));
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'design' => 'classic',
            'header' => true,
            'goal' => false,
            'image' => false,
            'sections' => [['givewp/donation-amount']],
            'gateways' => 1,
        ], $overrides);
    }
}
