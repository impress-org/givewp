<?php

namespace Give\Tests\Unit\DonationForms\Endpoints;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultGiveWPUsers;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * The donations list form filter loads its options from the core route for the give_forms post type.
 *
 * @since TBD
 */
class FormsPostTypeRouteTest extends RestApiTestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;
    use HasDefaultWordPressUsers;
    use HasDefaultGiveWPUsers;

    /**
     * @since TBD
     */
    public function testListsPublishedV2AndV3FormsByTitle()
    {
        $v2Form = $this->createSimpleDonationForm();
        $v3Form = DonationForm::factory()->create(['title' => 'Visual Builder Form']);
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);

        $request = $this->createRequest('GET', '/wp/v2/give_forms');
        $request->set_query_params(['_fields' => 'id,title', 'status' => 'publish', 'per_page' => 30]);

        $response = $this->dispatchRequest($request);
        $titles = array_column($response->get_data(), 'title', 'id');

        $this->assertSame(200, $response->get_status());
        $this->assertSame('Visual Builder Form', $titles[$v3Form->id]['rendered']);
        $this->assertSame($v2Form->title, $titles[$v2Form->id]['rendered']);
        $this->assertArrayNotHasKey($draftForm->id, $titles);

        $request->set_query_params(['search' => 'Visual Builder', 'search_columns' => 'post_title']);

        $this->assertSame([$v3Form->id], array_column($this->dispatchRequest($request)->get_data(), 'id'));
    }

    /**
     * @since TBD
     */
    public function testListsDraftFormsOnlyForUsersWhoCanEditForms()
    {
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);
        $query = ['_fields' => 'id,title,status', 'status' => 'publish,draft', 'per_page' => 30];

        $request = $this->createRequest('GET', '/wp/v2/give_forms', [], 'administrator');
        $request->set_query_params($query);
        $statuses = array_column($this->dispatchRequest($request)->get_data(), 'status', 'id');

        $this->assertSame('draft', $statuses[$draftForm->id]);

        $request = $this->createRequest('GET', '/wp/v2/give_forms', [], 'give_accountant');
        $request->set_query_params($query);

        $this->assertSame(400, $this->dispatchRequest($request)->get_status());
    }
}
