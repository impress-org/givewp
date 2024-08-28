<?php

namespace Give\Tests\Unit\Campaigns\Repositories;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass CampaignRepository
 */
final class CampaignRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnCampaign()
    {
        $campaignFactory = Campaign::factory()->create();
        $repository = new CampaignRepository();

        $campaign = $repository->getById($campaignFactory->id);

        $this->assertInstanceOf(Campaign::class, $campaignFactory);
        $this->assertEquals($campaignFactory->id, $campaign->id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInsertShouldAddCampaignToDatabase()
    {
        $campaignFactory = new Campaign(Campaign::factory()->definition());
        $repository = new CampaignRepository();

        $repository->insert($campaignFactory);

        $campaign = $repository->getById($campaignFactory->id);

        $this->assertEquals($campaign->getAttributes(), $campaignFactory->getAttributes());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $currentDate = Temporal::getCurrentDateTime();

        $campaignMissingStatus = new Campaign([
            'pageId' => 1,
            'type' => CampaignType::CORE(),
            'title' => __('GiveWP Campaign', 'give'),
            'shortDescription' => __('Campaign short description', 'give'),
            'longDescription' => __('Campaign long description', 'give'),
            'goal' => 10000000,
            'logo' => '',
            'image' => '',
            'primaryColor' => '#28C77B',
            'secondaryColor' => '#FFA200',
            'createdAt' => Temporal::withoutMicroseconds($currentDate),
            'startDate' => Temporal::withoutMicroseconds($currentDate),
            'endDate' => Temporal::withoutMicroseconds($currentDate->modify('+1 day')),
        ]);

        (new CampaignRepository())->insert($campaignMissingStatus);
    }


    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $currentDate = Temporal::getCurrentDateTime();

        $campaignMissingStatus = new Campaign([
            'pageId' => 1,
            'type' => CampaignType::CORE(),
            'title' => __('GiveWP Campaign', 'give'),
            'shortDescription' => __('Campaign short description', 'give'),
            'longDescription' => __('Campaign long description', 'give'),
            'goal' => 10000000,
            'logo' => '',
            'image' => '',
            'primaryColor' => '#28C77B',
            'secondaryColor' => '#FFA200',
            'createdAt' => Temporal::withoutMicroseconds($currentDate),
            'startDate' => Temporal::withoutMicroseconds($currentDate),
            'endDate' => Temporal::withoutMicroseconds($currentDate->modify('+1 day')),
        ]);

        (new CampaignRepository())->update($campaignMissingStatus);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateCampaignValuesInTheDatabase()
    {
        $repository = new CampaignRepository();
        $campaignFactory = Campaign::factory()->create();

        // update campaign
        $campaignFactory->title = 'Updated campaign title';
        $campaignFactory->shortDescription = 'Updated short description';
        $campaignFactory->type = CampaignType::PEER_TO_PEER();
        $campaignFactory->status = CampaignStatus::INACTIVE();

        $repository->update($campaignFactory);

        $campaign = $repository->prepareQuery()
            ->where('id', $campaignFactory->id)
            ->get();

        $this->assertNotEquals(CampaignType::CORE(), $campaign->type);
        $this->assertNotEquals(CampaignStatus::ACTIVE(), $campaign->status);
        $this->assertEquals('Updated campaign title', $campaign->title);
        $this->assertEquals('Updated short description', $campaign->shortDescription);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveCampaignFromTheDatabase()
    {
        $repository = new CampaignRepository();
        $campaignFactory = Campaign::factory()->create();

        $repository->delete($campaignFactory);

        $campaign = $repository->prepareQuery()
            ->where('id', $campaignFactory->id)
            ->get();

        $this->assertNull($campaign);
    }
}
