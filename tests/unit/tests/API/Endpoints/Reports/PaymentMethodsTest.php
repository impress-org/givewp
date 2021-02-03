<?php

final class PaymentMethodsTest extends Give_API_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->setDonations([
            [
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'live',
                'payment_gateway'      => 'stripe',
                'completed_date'       => '2021-02-01',
            ],
            [
                'payment_total'        => '100',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'live',
                'payment_gateway'      => 'stripe',
                'completed_date'       => '2021-02-01',
            ],
            [
                'payment_total'        => '300',
                'payment_currency'     => 'USD',
                'payment_mode'         => 'live',
                'payment_gateway'      => 'paypal-commerce',
                'completed_date'       => '2021-02-01',
            ]
        ]);
    }

    public function testYear() {

        $request = $this->makeRequest( 'GET', '/give-api/v2/reports/payment-methods', [
            'start' => '2021-01-01',
            'end' => '2038-12-31',
            'currency' => 'USD',
            'testMode' => false,
        ]);
        
        $response = $this->server->dispatch( $request );

        $labels = $response->get_data()[ 'data' ][ 'labels' ];
        $stripeIndex = array_search( 'Stripe - Credit Card', $labels );
        $paypalIndex = array_search( 'PayPal Donations', $labels );

        $this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( "200", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ $stripeIndex ] );
		$this->assertEquals( "300", $response->get_data()[ 'data' ][ 'datasets' ][ 0 ][ 'data' ][ $paypalIndex ] );
    }
}
