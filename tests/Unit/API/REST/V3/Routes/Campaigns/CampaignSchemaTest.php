<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Campaigns;

use DateTime;
use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use WP_REST_Server;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;

/**
 * @since 4.13.0
 */
class CampaignSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
        * @since 4.13.0
     */
    public function testCampaignSchemaShouldMatchActualResponse()
    {
        $campaign = Campaign::factory()->create();
        $donations = Donation::factory()->count(3)->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
        ]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual campaign data
        $dataRoute = '/' . CampaignRoute::NAMESPACE . '/' . 'campaigns' . '/' . $campaign->id;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
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

    /**
     * @since 4.13.0
     */
    public function testCampaignCollectionSchemaShouldMatchActualResponse()
    {
        /** @var Campaign[] $campaigns */
        $campaign = Campaign::factory()->count(3)->create();

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual collection data
        $dataRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Assert that we have data in the collection
        $this->assertNotEmpty($actualData, 'Collection should contain at least one campaign');
        $this->assertIsArray($actualData, 'Collection should be an array');

        // Validate first item in collection
        if (!empty($actualData)) {
            $firstItem = $actualData[0];
            $this->validateSchemaProperties($schema, $firstItem);
            $this->validateDataTypes($schema, $firstItem);
            $this->validateEnumValues($schema, $firstItem);
        }
    }

    /**
     * @since 4.13.0
     */
    public function testCampaignStatisticsSchemaShouldMatchActualResponse()
    {
        $campaign = Campaign::factory()->create();
        $donations = Donation::factory()->count(3)->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
        ]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS . '/' . $campaign->id . '/statistics';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual campaign statistics data
        $dataRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS . '/' . $campaign->id . '/statistics';
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
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

    /**
     * @since 4.13.0
     */
    public function testCampaignRevenueSchemaShouldMatchActualResponse()
    {
        $campaign = Campaign::factory()->create();
        $donations = Donation::factory()->count(3)->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
        ]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS . '/' . $campaign->id . '/revenue';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual campaign revenue data
        $dataRoute = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS . '/' . $campaign->id . '/revenue';
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
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
