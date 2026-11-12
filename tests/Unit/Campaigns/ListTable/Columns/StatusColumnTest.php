<?php

namespace Give\Tests\Unit\Campaigns\ListTable\Columns;

use Give\Campaigns\ListTable\Columns\StatusColumn;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class StatusColumnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testBadgeUsesTheVocabularyLabelForEveryStatus(): void
    {
        foreach (CampaignStatus::values() as $status) {
            $campaign = Campaign::factory()->create(['status' => $status]);

            $this->assertStringContainsString(
                '<p>' . $status->label() . '</p>',
                (new StatusColumn())->getCellValue($campaign)
            );
        }
    }
}
