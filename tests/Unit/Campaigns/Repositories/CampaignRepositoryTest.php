<?php

namespace Give\Tests\Unit\Campaigns\Repositories;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
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
    public function testGetByFormIdShouldReturnCampaign()
    {
        $campaignFactory = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $repository = new CampaignRepository();
        $repository->addCampaignForm($campaignFactory, $form->id);

        $campaign = $repository->getByFormId($form->id);

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
        $campaignFactory->status = CampaignStatus::INACTIVE();

        $repository->update($campaignFactory);

        $campaign = $repository->prepareQuery()
            ->where('id', $campaignFactory->id)
            ->get();

        $this->assertNotEquals(CampaignStatus::ACTIVE()->getValue(), $campaign->status->getValue());
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

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testPeerToPeerCampaignsAreExcludedFromQuery()
    {
        $repository = new CampaignRepository();

        $p2p_campaign = Campaign::factory()->create([
            'type' => CampaignType::PEER_TO_PEER(),
        ]);

        $this->assertNull($repository->getById($p2p_campaign->id));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testPeerToPeerCampaignsAreExcludedFromCount()
    {
        $repository = new CampaignRepository();

        Campaign::factory()->create([
            'type' => CampaignType::CORE(),
        ]);

        Campaign::factory()->create([
            'type' => CampaignType::PEER_TO_PEER(),
        ]);

        $this->assertEquals(1, $repository->prepareQuery()->count());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testAddCampaignFormShouldAddNewFormToCampaign()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var DonationForm $form */
        $newForm = DonationForm::factory()->create();

        $repository = new CampaignRepository();
        $repository->addCampaignForm($campaign, $newForm->id);

        $campaignReturn = $repository->getByFormId($newForm->id);

        $this->assertEquals($campaignReturn->getAttributes(), $campaign->getAttributes());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testAddCampaignFormShouldAddNewDefaultFormToCampaign()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var DonationForm $form */
        $newDefaultForm = DonationForm::factory()->create();

        $repository = new CampaignRepository();
        $repository->addCampaignForm($campaign, $newDefaultForm->id, true);

        //Re-fetch
        $campaign = $campaign::find($campaign->id);

        $this->assertEquals($newDefaultForm->id, $campaign->defaultForm()->id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateCampaignFormShouldUpdateDefaultFormToCampaign()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var DonationForm $form1 */
        $form1 = DonationForm::factory()->create();

        /** @var DonationForm $form2 */
        $form2 = DonationForm::factory()->create();

        $repository = new CampaignRepository();
        $repository->addCampaignForm($campaign, $form1->id, true);
        $repository->addCampaignForm($campaign, $form2->id);
        $repository->updateDefaultCampaignForm($campaign, $form2->id);

        //Re-fetch
        $campaign = Campaign::find($campaign->id);

        $this->assertEquals($form2->id, $campaign->defaultForm()->id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMergeCampaignsShouldReturnTrue()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        $repository = new CampaignRepository();
        $merged = $repository->mergeCampaigns([$campaign1, $campaign2], $destinationCampaign);

        $this->assertTrue($merged);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMergeCampaignsShouldMigrateFormsToDestinationCampaign()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        $formCampaign1 = $campaign1->defaultForm();
        $formCampaign2 = $campaign2->defaultForm();

        $repository = new CampaignRepository();
        $repository->mergeCampaigns([$campaign1, $campaign2], $destinationCampaign);

        //Re-fetch
        $destinationCampaign = Campaign::find($destinationCampaign->id);

        $campaignReturn = $repository->getByFormId($formCampaign1->id);
        $this->assertEquals($campaignReturn->getAttributes(), $destinationCampaign->getAttributes());

        $campaignReturn = $repository->getByFormId($formCampaign2->id);
        $this->assertEquals($campaignReturn->getAttributes(), $destinationCampaign->getAttributes());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMergeCampaignsShouldMigrateRevenueToDestinationCampaign()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        /** @var Donation $donationCampaign1 */
        $donationCampaign1 = Donation::factory()->create(['formId' => $campaign1->defaultForm()->id]);
        /** @var Donation $donationCampaign2 */
        $donationCampaign2 = Donation::factory()->create(['formId' => $campaign2->defaultForm()->id]);

        // TODO Remove this updates clauses when the logic to automatically set the campaign_id in the revenue table entries for new donations is implemented
        DB::query(
            DB::prepare('UPDATE ' . DB::prefix('give_revenue') . ' SET campaign_id = %d WHERE donation_id = %d',
                [
                    $campaign1->id,
                    $donationCampaign1->id,
                ])
        );
        DB::query(
            DB::prepare('UPDATE ' . DB::prefix('give_revenue') . ' SET campaign_id = %d WHERE donation_id = %d',
                [
                    $campaign2->id,
                    $donationCampaign2->id,
                ])
        );

        $repository = new CampaignRepository();
        $repository->mergeCampaigns([$campaign1, $campaign2], $destinationCampaign);

        $revenueEntry = DB::table('give_revenue')->where('donation_id', $donationCampaign1->id)->get();
        $this->assertEquals($destinationCampaign->id, $revenueEntry->campaign_id);

        $revenueEntry = DB::table('give_revenue')->where('donation_id', $donationCampaign2->id)->get();
        $this->assertEquals($destinationCampaign->id, $revenueEntry->campaign_id);
    }


    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMergeCampaignsShouldDeleteMergedCampaigns()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        $repository = new CampaignRepository();
        $repository->mergeCampaigns([$campaign1, $campaign2], $destinationCampaign);;

        $this->assertNull(Campaign::find($campaign1->id));
        $this->assertNull(Campaign::find($campaign2->id));
    }
}
