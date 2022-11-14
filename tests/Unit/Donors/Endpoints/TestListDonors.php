<?php

namespace GiveTests\Unit\Donors\Endpoints;

use Exception;
use Give\Donors\Endpoints\ListDonors;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonors extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShowShouldReturnListDonorsData()
    {
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);

        $listDonors = new ListDonors();

        $response = $listDonors->handleRequest($mockRequest);

        $this->assertSame([
            'items' => [],
            'totalItems' => 0,
            'totalPages' => 1
        ], $response);
    }

    /**
     *
     * @unreleased
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/donors'
        );
    }
}

