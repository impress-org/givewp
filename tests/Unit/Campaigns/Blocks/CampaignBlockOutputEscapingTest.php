<?php

namespace Give\Tests\Unit\Campaigns\Blocks;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Shortcodes\CampaignGridShortcode;
use Give\Campaigns\Shortcodes\CampaignShortcode;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class CampaignBlockOutputEscapingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A value that must survive rendering as data, not markup.
     */
    const PAYLOAD = "x' onfocus='alert(document.domain)' autofocus='' tabindex='0' x='";

    /**
     * @since TBD
     */
    public function testGridShortcodeEscapesAttributeValuesInOutput(): void
    {
        $html = (new CampaignGridShortcode())->renderShortcode([
            'filter_by' => self::PAYLOAD,
        ]);

        $this->assertStringNotContainsString("onfocus='alert", $html);
        $this->assertStringContainsString('&#039;', $html);

        preg_match('/data-attributes="([^"]+)"/', $html, $matches);
        $attributes = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5), true);

        $this->assertSame(self::PAYLOAD, $attributes['filterBy']);
    }

    /**
     * @since TBD
     */
    public function testCampaignShortcodeEscapesAttributeValuesInOutput(): void
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $html = (new CampaignShortcode())->renderShortcode([
            'campaign_id' => $campaign->id,
        ]);

        $this->assertMatchesRegularExpression('/data-attributes="([^"]+)"/', $html);

        preg_match('/data-attributes="([^"]+)"/', $html, $matches);
        $attributes = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5), true);

        $this->assertSame((int)$campaign->id, $attributes['campaignId']);
    }
}