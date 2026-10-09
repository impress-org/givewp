<?php

namespace Give\Tests\Feature\DonationForms;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormsRoute;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\FormBuilder\Routes\CreateFormRoute;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\InterruptsRedirects;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * A donation form that belongs to no campaign, from the Add form button through the forms list to
 * linking it to a campaign later.
 *
 * @since TBD
 */
final class StandaloneFormsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use InterruptsRedirects;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // Other tests define DOING_AJAX, which cannot be undone and sends wp_die() to the ajax die handler.
        // Route that handler to the test handler too, so wp_die() throws WPDieException in every run order.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['page'], $_GET['donationFormID'], $_REQUEST['_wpnonce']);
        wp_set_current_user(0);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testAddFormCreatesADraftFormWithNoCampaign(): void
    {
        $formId = $this->createFormThroughTheFormBuilder();
        $form = DonationForm::find($formId);

        $this->assertNotNull($form);
        $this->assertTrue($form->status->isDraft());
        $this->assertNull(Campaign::findByFormId($formId));
        $this->assertSame(0, give_derive_campaign_id_from_form_id($formId));
    }

    /**
     * @since TBD
     */
    public function testAddFormNeedsTheNonceAndTheCapability(): void
    {
        $countForms = static function (): int {
            return count(get_posts(['post_type' => 'give_forms', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']));
        };
        $before = $countForms();

        $_GET['page'] = FormBuilderRouteBuilder::SLUG;
        $_GET['donationFormID'] = 'new';

        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $_REQUEST['_wpnonce'] = wp_create_nonce(FormBuilderRouteBuilder::CREATE_NONCE_ACTION);
        (new CreateFormRoute())();
        $this->assertSame($before, $countForms());

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        unset($_REQUEST['_wpnonce']);

        try {
            (new CreateFormRoute())();
            $this->fail('Expected the missing nonce to stop the request.');
        } catch (\WPDieException $exception) {
            $this->assertSame($before, $countForms());
        }
    }

    /**
     * @since TBD
     */
    public function testAStandaloneFormListsWithNoCampaignUntilItIsLinkedToOne(): void
    {
        $formId = $this->createFormThroughTheFormBuilder();
        $campaign = Campaign::factory()->create(['title' => 'Spring Appeal']);

        $row = $this->findListRow($formId);
        $this->assertSame(0, $row['campaignId']);
        $this->assertSame('No campaign', $row['formCampaign']);
        $this->assertContains($formId, array_column($this->listForms(['campaign' => 'none']), 'id'));

        $request = $this->createRequest(
            WP_REST_Server::CREATABLE,
            '/' . DonationFormsRoute::NAMESPACE . '/' . DonationFormsRoute::ASSOCIATE_FORMS_WITH_CAMPAIGN,
            [],
            'administrator'
        );
        $request->set_body_params(['formIDs' => [$formId], 'campaignId' => $campaign->id]);

        $this->assertSame(200, $this->dispatchRequest($request)->get_status());

        $row = $this->findListRow($formId);
        $this->assertSame($campaign->id, $row['campaignId']);
        $this->assertStringContainsString('Spring Appeal', $row['formCampaign']);
        $this->assertNotContains($formId, array_column($this->listForms(['campaign' => 'none']), 'id'));
        $this->assertSame($campaign->id, give_derive_campaign_id_from_form_id($formId));
    }

    /**
     * Runs the admin_init route the forms list's Add form button points at and returns the id of
     * the form it redirects to.
     *
     * @since TBD
     */
    private function createFormThroughTheFormBuilder(): int
    {
        $_GET['page'] = FormBuilderRouteBuilder::SLUG;
        $_GET['donationFormID'] = 'new';

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $_REQUEST['_wpnonce'] = wp_create_nonce(FormBuilderRouteBuilder::CREATE_NONCE_ACTION);

        $location = $this->captureRedirect(new CreateFormRoute());
        parse_str((string)parse_url($location, PHP_URL_QUERY), $query);

        $this->assertNotEmpty($query['donationFormID'] ?? null, "Expected a redirect to the new form, got $location");

        return (int)$query['donationFormID'];
    }

    /**
     * @since TBD
     */
    private function listForms(array $params = []): array
    {
        $request = $this->createRequest(WP_REST_Server::READABLE, '/give-api/v2/admin/forms', [], 'administrator');
        $request->set_query_params(array_merge(['page' => 1, 'perPage' => 30, 'status' => 'any'], $params));

        $response = $this->dispatchRequest($request);
        $this->assertSame(200, $response->get_status());

        return $response->get_data()['items'];
    }

    /**
     * @since TBD
     */
    private function findListRow(int $formId): array
    {
        $rows = array_column($this->listForms(), null, 'id');
        $this->assertArrayHasKey($formId, $rows);

        return $rows[$formId];
    }
}
