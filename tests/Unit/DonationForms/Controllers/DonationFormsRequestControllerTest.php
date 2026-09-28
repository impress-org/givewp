<?php

namespace Give\Tests\Unit\DonationForms\Controllers;

use Give\Campaigns\Models\Campaign;
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
    public function testGetFormsFiltersByTitleSearch()
    {
        $match = DonationForm::factory()->create(['title' => 'Winter Appeal']);
        DonationForm::factory()->create(['title' => 'Summer Gala']);

        $request = new WP_REST_Request('GET', '/givewp/v3/forms');
        $request->set_param('status', ['publish']);
        $request->set_param('ids', []);
        $request->set_param('page', 1);
        $request->set_param('per_page', 30);
        $request->set_param('search', 'Winter');

        $forms = (new DonationFormsRequestController())->getForms($request)->get_data();

        $this->assertSame([$match->id], array_column($forms, 'id'));
    }
}
