<?php

final class IncomeTest extends Give_API_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->setDonations([
            [
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-01-01',
            ],
            [
                'payment_total'        => '200',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-02',
            ],
            [
                'payment_total'        => '300',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'completed_date'       => '2021-02-02',
            ]
        ]);
    }

    public function testYear() {

        $request = $this->makeRequest( 'GET', '/give-api/v2/reports/income', [
            'start' => '2021-01-01',
            'end' => '2022-01-01',
            'currency' => 'USD',
            'testMode' => 1,
        ]);
        
        $response = $this->server->dispatch( $request );

        $this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( "100", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ 1 ][ 'y' ] );
		$this->assertEquals( "500", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ 2 ][ 'y' ] );
    }
}
