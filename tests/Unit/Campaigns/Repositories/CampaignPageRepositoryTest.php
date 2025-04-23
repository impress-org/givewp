<?php

namespace Give\Tests\Unit\Campaigns\Repositories;

use Exception;
use Give\Campaigns\Actions\CreateDefaultLayoutForCampaignPage;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Repositories\CampaignPageRepository;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 *
 * @coversDefaultClass CampaignPageRepository
 */
final class CampaignPageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnCampaignPage()
    {
        $campaign = Campaign::factory()->create();
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
        ]);

        $campaignPageFresh = give(CampaignPageRepository::class)->getById($campaignPage->id);

        $this->assertInstanceOf(CampaignPage::class, $campaignPageFresh);
        $this->assertEquals($campaignPage->id, $campaignPageFresh->id);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testInsertShouldAddCampaignPageToDatabase()
    {
        $campaign = Campaign::factory()->create();
        $campaignPage = new CampaignPage([
            'campaignId' => $campaign->id,
            'content' => give(CreateDefaultLayoutForCampaignPage::class)(
                $campaign->id,
                $campaign->shortDescription
            )
        ]);

        give(CampaignPageRepository::class)->insert($campaignPage);

        $campaignPageFresh = give(CampaignPageRepository::class)->getById($campaignPage->id);

        $this->assertEquals($campaignPage->getAttributes(), $campaignPageFresh->getAttributes());
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignPageInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $campaignPageMissingCampaignId = new CampaignPage([
            // Note: `campaignId` intentionally not set.
        ]);

        (new CampaignPageRepository())->insert($campaignPageMissingCampaignId);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignPageUpdateShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $campaignPageMissingCampaignId = new CampaignPage([
            // Note: `campaignId` intentionally not set.
        ]);

        (new CampaignPageRepository())->update($campaignPageMissingCampaignId);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignPageUpdateShouldUpdateCampaignPageValuesInTheDatabase()
    {
        $campaign1 = Campaign::factory()->create();
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign1->id,
        ]);

        $campaign2 = Campaign::factory()->create();
        $campaignPage->campaignId = $campaign2->id;
        give(CampaignPageRepository::class)->update($campaignPage);

        $campaignPageFresh = give(CampaignPageRepository::class)->getById($campaignPage->id);

        $this->assertEquals($campaign2->id, $campaignPageFresh->campaignId);
        $this->assertNotEquals($campaign1->id, $campaignPageFresh->campaignId);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignPageDeleteShouldRemoveCampaignPageFromTheDatabase()
    {
        $campaign = Campaign::factory()->create();
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
        ]);

        give(CampaignPageRepository::class)->delete($campaignPage);

        $campaignPageFresh = CampaignPage::find($campaignPage->id);

        $this->assertNull($campaignPageFresh);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignPageShouldBeCreatedWithCampaignTitle()
    {
        $campaign = Campaign::factory()->create();
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
        ]);

        $this->assertEquals($campaign->title, get_the_title($campaignPage->id));
    }
}
