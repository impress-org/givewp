<?php

namespace Give\Tests\Unit\Campaigns\Blocks;

use Give\Campaigns\Blocks\CampaignDonors\CampaignDonorsBlockViewModel;
use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Block_Supports;

/**
 * @since TBD
 */
final class CampaignDonorsBlockDonorNameEscapingTest extends TestCase
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
     * Both the donor name and the donor company are rendered on a public page and must have
     * their shortcode syntax neutralized before output.
     *
     * @since TBD
     */
    public function testNeutralizesShortcodesInDonorNameAndCompany(): void
    {
        $campaign = Campaign::factory()->create();

        $nameInput = '[svul[' . self::TAG . ']tag]Ada';
        $companyInput = '[' . self::TAG . ']Acme';

        $donor = (object)[
            'name' => $nameInput,
            'company' => $companyInput,
            'isAnonymous' => false,
            'amount' => '10.00',
            'avatarId' => 0,
            'email' => 'donor@example.test',
        ];

        $attributes = [
            'sortBy' => 'recent-donors',
            'showButton' => false,
            'showAvatar' => false,
            'showCompanyName' => true,
        ];

        $html = $this->renderViewModel($campaign, [$donor], $attributes);

        $expectedName = esc_html(give_strip_shortcodes_deep($nameInput));
        $expectedCompany = esc_html(give_strip_shortcodes_deep($companyInput));

        $this->assertStringContainsString($expectedName, $html);
        $this->assertStringContainsString($expectedCompany, $html);

        // The raw payloads and the executed shortcode output must never reach the page.
        $this->assertStringNotContainsString($nameInput, $html);
        $this->assertStringNotContainsString($companyInput, $html);
        $this->assertStringNotContainsString('[' . self::TAG . ']', $html);
        $this->assertStringNotContainsString('RENDERED', $html);
    }

    /**
     * Capture the block's rendered HTML.
     *
     * @since TBD
     */
    private function renderViewModel(Campaign $campaign, array $donors, array $attributes): string
    {
        // get_block_wrapper_attributes() in the view requires an active block-supports context.
        WP_Block_Supports::$block_to_render = ['blockName' => 'givewp/campaign-donors', 'attrs' => []];

        ob_start();
        (new CampaignDonorsBlockViewModel($campaign, $donors, $attributes))->render();
        $html = (string)ob_get_clean();

        WP_Block_Supports::$block_to_render = null;

        return $html;
    }
}
