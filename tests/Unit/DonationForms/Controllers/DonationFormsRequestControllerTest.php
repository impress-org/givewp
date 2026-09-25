<?php

namespace Give\Tests\Unit\DonationForms\Controllers;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\DonationForms\Controllers\DonationFormsRequestController;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * @since TBD
 */
class DonationFormsRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testAssociateFormsWithCampaignLinksFormAndQueuesCacheRefresh()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $request = new WP_REST_Request('POST', '/givewp/v3/associate-forms-with-campaign');
        $request->set_param('campaignId', $campaign->id);
        $request->set_param('formIDs', [$form->id]);

        $response = (new DonationFormsRequestController())->associateFormsWithCampaign($request);

        $this->assertSame(200, $response->get_status());
        $this->assertSame($campaign->id, Campaign::findByFormId($form->id)->id);
        $this->assertTrue(
            as_has_scheduled_action('givewp_cache_campaign_data', [$campaign->id], 'givewp_campaigns_cache')
        );
    }

    /**
     * @since TBD
     */
    public function testAssociateFormsWithCampaignMovesALinkedFormAndRefreshesBothCampaigns(): void
    {
        $from = Campaign::factory()->create();
        $to = Campaign::factory()->create();
        $form = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($from, $form->id);

        $request = new WP_REST_Request('POST', '/givewp/v3/associate-forms-with-campaign');
        $request->set_param('campaignId', $to->id);
        $request->set_param('formIDs', [$form->id]);

        $response = (new DonationFormsRequestController())->associateFormsWithCampaign($request);

        $this->assertSame(200, $response->get_status());
        $this->assertSame($to->id, Campaign::findByFormId($form->id)->id);
        $this->assertTrue(as_has_scheduled_action('givewp_cache_campaign_data', [$to->id], 'givewp_campaigns_cache'));
        $this->assertTrue(as_has_scheduled_action('givewp_cache_campaign_data', [$from->id], 'givewp_campaigns_cache'));
    }

    /**
     * @since TBD
     */
    public function testAssociateFormsWithCampaignRefusesToMoveADefaultForm(): void
    {
        $from = Campaign::factory()->create();
        $to = Campaign::factory()->create();

        $request = new WP_REST_Request('POST', '/givewp/v3/associate-forms-with-campaign');
        $request->set_param('campaignId', $to->id);
        $request->set_param('formIDs', [$from->defaultFormId]);

        $response = (new DonationFormsRequestController())->associateFormsWithCampaign($request);

        $this->assertSame(400, $response->get_status());
        $this->assertStringContainsString('default form', $response->get_data()['message']);
        $this->assertSame($from->id, Campaign::findByFormId($from->defaultFormId)->id);
    }

    /**
     * @since TBD
     */
    public function testAssociateFormsWithCampaignMovesNothingWhenOneFormIsADefaultForm(): void
    {
        $from = Campaign::factory()->create();
        $to = Campaign::factory()->create();
        $movable = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($from, $movable->id);

        $request = new WP_REST_Request('POST', '/givewp/v3/associate-forms-with-campaign');
        $request->set_param('campaignId', $to->id);
        $request->set_param('formIDs', [$movable->id, $from->defaultFormId]);

        $response = (new DonationFormsRequestController())->associateFormsWithCampaign($request);

        $this->assertSame(400, $response->get_status());
        $this->assertSame($from->id, Campaign::findByFormId($movable->id)->id);
    }

    /**
     * @since TBD
     */
    public function testAssociateFormsWithCampaignRefusesAPeerToPeerForm(): void
    {
        $to = Campaign::factory()->create();
        $form = DonationForm::factory()->create();
        $peerToPeer = Campaign::factory()->create();
        DB::table('give_campaigns')
            ->where('id', $peerToPeer->id)
            ->update(['campaign_type' => CampaignType::PEER_TO_PEER, 'form_id' => $form->id]);

        $request = new WP_REST_Request('POST', '/givewp/v3/associate-forms-with-campaign');
        $request->set_param('campaignId', $to->id);
        $request->set_param('formIDs', [$form->id]);

        $response = (new DonationFormsRequestController())->associateFormsWithCampaign($request);

        $this->assertSame(400, $response->get_status());
        $this->assertNull(Campaign::findByFormId($form->id));
    }

    /**
     * @since TBD
     */
    public function testDetachFormsFromCampaignLeavesTheFormStandaloneAndRefreshesTheCampaign(): void
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($campaign, $form->id);

        $request = new WP_REST_Request('POST', '/givewp/v3/detach-forms-from-campaign');
        $request->set_param('formIDs', [$form->id]);

        $response = (new DonationFormsRequestController())->detachFormsFromCampaign($request);

        $this->assertSame(200, $response->get_status());
        $this->assertNull(Campaign::findByFormId($form->id));
        $this->assertTrue(as_has_scheduled_action('givewp_cache_campaign_data', [$campaign->id], 'givewp_campaigns_cache'));
    }

    /**
     * @since TBD
     */
    public function testDetachFormsFromCampaignRefusesTheDefaultForm(): void
    {
        $campaign = Campaign::factory()->create();

        $request = new WP_REST_Request('POST', '/givewp/v3/detach-forms-from-campaign');
        $request->set_param('formIDs', [$campaign->defaultFormId]);

        $response = (new DonationFormsRequestController())->detachFormsFromCampaign($request);

        $this->assertSame(400, $response->get_status());
        $this->assertSame($campaign->id, Campaign::findByFormId($campaign->defaultFormId)->id);
    }
}
