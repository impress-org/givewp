<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Framework\Database\DB;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;
use WP_REST_Server;

/**
 * @since 4.14.0
 */
class DonorNoteSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
     * @since 4.14.0
     */
    public function testDonorNoteSchemaShouldMatchActualResponse()
    {
        DB::query("DELETE FROM " . DB::prefix('give_comments'));

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = $this->createDonorNote($donor->id);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual note data
        $dataRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
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
     * @since 4.14.0
     */
    public function testDonorNoteCollectionSchemaShouldMatchActualResponse()
    {
        DB::query("DELETE FROM " . DB::prefix('give_comments'));

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = $this->createDonorNote($donor->id);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual collection data
        $dataRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Assert that we have data in the collection
        $this->assertNotEmpty($actualData, 'Collection should contain at least one note');
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
     * @since 4.14.0
     */
    public function testDateFormatsShouldBeConsistentWithWordPressStandards()
    {
        DB::query("DELETE FROM " . DB::prefix('give_comments'));

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = $this->createDonorNote($donor->id);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $response = $this->dispatchRequest($request);
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Check if dates are in WordPress standard format (ISO 8601 without timezone)
        if (isset($data['createdAt'])) {
            $this->validateDateFormat($data['createdAt'], 'createdAt');
        }

        if (isset($data['updatedAt'])) {
            $this->validateDateFormat($data['updatedAt'], 'updatedAt');
        }
    }

    /**
     * @since 4.14.0
     *
     * @throws Exception
     */
    private function createDonorNote(int $donorId): DonorNote
    {
        return DonorNote::create([
            'donorId' => $donorId,
            'content' => 'Test note content',
            'type' => DonorNoteType::ADMIN(),
        ]);
    }
}
