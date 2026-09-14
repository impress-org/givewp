<?php

namespace Give\Tests\Unit\Campaigns\Blocks;

use Give\Campaigns\Blocks\CampaignDonations\CampaignDonationsBlockViewModel;
use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Block_Supports;

/**
 * @since TBD
 */
final class CampaignDonationsBlockDonorNameEscapingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A registered shortcode tag used to build deterministic payloads.
     */
    const TAG = 'svultag';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_shortcode(self::TAG, static function () {
            return 'RENDERED';
        });
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_shortcode(self::TAG);

        parent::tearDown();
    }

    /**
     * The donor name rendered by the donations block must have its shortcode syntax neutralized,
     * including a self-nested tag that survives a single strip pass.
     *
     * @since TBD
     */
    public function testNeutralizesShortcodesInDonorName(): void
    {
        $campaign = Campaign::factory()->create();

        $nameInput = '[svul[' . self::TAG . ']tag]Ada';

        $donation = (object)[
            'donorName' => $nameInput,
            'isAnonymous' => false,
            'amount' => '10.00',
            'date' => '2026-01-01 00:00:00',
            'donorAvatarId' => 0,
            'email' => 'donor@example.test',
        ];

        $attributes = [
            'sortBy' => 'recent-donations',
            'showButton' => false,
            'showIcon' => false,
        ];

        $html = $this->renderViewModel($campaign, [$donation], $attributes);

        $expectedName = esc_html(give_strip_shortcodes_deep($nameInput));

        $this->assertStringContainsString($expectedName, $html);
        $this->assertStringNotContainsString($nameInput, $html);
        $this->assertStringNotContainsString('[' . self::TAG . ']', $html);
        $this->assertStringNotContainsString('RENDERED', $html);
    }

    /**
     * Capture the block's rendered HTML.
     *
     * @since TBD
     */
    private function renderViewModel(Campaign $campaign, array $donations, array $attributes): string
    {
        // get_block_wrapper_attributes() in the view requires an active block-supports context.
        WP_Block_Supports::$block_to_render = ['blockName' => 'givewp/campaign-donations', 'attrs' => []];

        ob_start();
        (new CampaignDonationsBlockViewModel($campaign, $donations, $attributes))->render();
        $html = (string)ob_get_clean();

        WP_Block_Supports::$block_to_render = null;

        return $html;
    }
}
