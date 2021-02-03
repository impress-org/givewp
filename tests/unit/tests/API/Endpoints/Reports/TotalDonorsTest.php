<?php

final class TotalDonorsTest extends Give_API_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->setDonations([
            [
                'donor_id'             => 1,
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-01',
            ],
            [
                'donor_id'             => 2,
                'payment_total'        => '200',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-01',
            ]
        ]);
    }

    public function testYear() {

        $request = $this->makeRequest( 'GET', '/give-api/v2/reports/total-donors', [
            'start' => '2021-01-01',
            'end' => '2038-12-31',
            'currency' => 'USD',
            'testMode' => 1,
        ]);
        
        $response = $this->server->dispatch( $request );

        $this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( "2", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'highlight' ] );
    }
}
