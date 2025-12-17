<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonorStatisticsSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
     * @unreleased
     */
    public function testDonorStatisticsSchemaShouldMatchActualResponse()
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Create a donation to ensure statistics are available
        $this->createDonation($donor->id);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/statistics';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual statistics data
        $dataRoute = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/statistics';
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Validate that all required schema properties exist in actual response
        $this->validateSchemaProperties($schema, $actualData);

        // Validate data types match schema
        $this->validateDataTypes($schema, $actualData);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createDonation(int $donorId): Donation
    {
        return Donation::factory()->create([
            'donorId' => $donorId,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(10000, 'USD'),
            'mode' => DonationMode::LIVE(),
        ]);
    }
}
