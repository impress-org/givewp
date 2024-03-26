<?php

namespace Give\Tests\Feature\FormMigration\Controllers;

use Give\FormMigration\Actions\GetMigratedFormId;
use Give\FormMigration\Controllers\MigrationController;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;


/**
 * @since 3.4.0
 *
 * @covers \Give\FormMigration\Controllers\MigrationController
 */
class TestMigrationController extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.4.0
     */
    public function testShouldMigrateFormV2ToV3(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $controller = new MigrationController($request);

        $response = $controller($formV2);

        $formV3Id = (int)(new GetMigratedFormId)($formV2->id);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            'v2FormId' => $formV2->id,
            'v3FormId' => $formV3Id,
             'redirect' => add_query_arg([
                'post_type' => 'give_forms',
                'page' => 'givewp-form-builder',
                'donationFormID' => $formV3Id,
            ], admin_url('edit.php'))
        ]);
    }

    /**
     *
     * @since 3.4.0
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            '/wp/v2/' . 'admin/forms/migrate/(?P<id>\d+)'
        );
    }
}
