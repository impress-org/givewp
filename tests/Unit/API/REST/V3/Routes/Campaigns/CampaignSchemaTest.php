<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Campaigns;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use WP_REST_Server;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;

class CampaignSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
        * @unreleased
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
}
