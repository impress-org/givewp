<?php

final class TotalIncomeTest extends Give_API_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->setDonations([
            [
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-01',
            ],
            [
                'payment_total'        => '200',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-01',
            ]
        ]);
    }

    public function testTotalIncome() {

        $request = $this->makeRequest( 'GET', '/give-api/v2/reports/total-income', [
            'start' => '2021-01-01',
            'end' => '2038-12-31',
            'currency' => 'USD',
            'testMode' => 1,
        ]);
        
        $response = $this->server->dispatch( $request );

        $this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( "$300.00", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'highlight' ] );
    }
}
