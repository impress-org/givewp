<?php

final class PaymentStatusesTest extends Give_API_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->setDonations([
            [
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'payment_status'       => 'publish',
                'completed_date'       => '2021-02-01',
            ],
            [
                'payment_total'        => '200',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'payment_status'       => 'cancelled',
                'completed_date'       => '2021-02-01',
            ],
            [
                'payment_total'        => '300',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'test',
                'payment_status'       => 'cancelled',
                'completed_date'       => '2021-02-01',
            ],
        ]);
    }

    public function testYear() {

        $request = $this->makeRequest( 'GET', '/give-api/v2/reports/payment-statuses', [
            'start' => '2021-01-01',
            'end' => '2038-12-31',
            'currency' => 'USD',
            'testMode' => false,
        ]);
        
        $response = $this->server->dispatch( $request );

        $labels = $response->get_data()[ 'data' ][ 'labels' ];
        $publishIndex = array_search( 'Completed', $labels );
        $cancelledIndex = array_search( 'Cancelled', $labels );

        $this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( "100", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ $publishIndex ] );
		$this->assertEquals( "500", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ $cancelledIndex ] );
    }
}
