<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Exception;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use WP_REST_Server;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;

class DonationSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
     * TODO: add customfields and eventTickets
     * @unreleased
     */
    public function testDonationSchemaShouldMatchActualResponse()
    {
        add_filter('givewp_donation_details_custom_fields', function ($customFields) {
            $customFields[] = [
                'label' => 'Custom Text Field',
                'value' => 'Custom Text Field Value',
            ];

            return $customFields;
        });

        $donation = Donation::factory()->create([
            'anonymous' => false,
        ]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual donation data
        $dataRoute = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataRequest->set_query_params(['includeSensitiveData' => true, 'anonymousDonations' => 'include']);
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Validate that all required schema properties exist in actual response
        $this->validateSchemaProperties($schema, $actualData);

        // Validate data types match schema
        $this->validateDataTypes($schema, $actualData);

        // Validate enum values match schema
        $this->validateEnumValues($schema, $actualData);
    }
}
