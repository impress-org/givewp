<?php

namespace Give\DonorProfiles\Tabs\DonationHistoryTab;

use WP_REST_Request;
use WP_REST_Response;
use Give\DonorProfiles\Tabs\Contracts\Route as RouteAbstract;
use Give\DonorProfiles\Repositories\Donations as DonationsRepository;

/**
 * @unreleased
 */
class DonationsRoute extends RouteAbstract {

	/**
	 * @return string
	 */
	public function endpoint() {
		return 'donations';
	}

	/**
	 * @return array
	 */
	public function args() {
		return [];
	}

	/**
	 * @unreleased
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 *
	 */
	public function handleRequest( $request ) {
		$donorId = give()->donorProfile->getId();

		$repository = new DonationsRepository();

		return $this->getData( $repository, $donorId );
	}

	/**
	 * @unreleased
	 *
	 * @param  DonationsRepository  $repository
	 * @param $donorId
	 *
	 * @return WP_REST_Response
	 */
	protected function getData( DonationsRepository $repository, $donorId ) {
		$donations = $repository->getDonations( $donorId );
		$count     = $repository->getDonationCount( $donorId );
		$revenue   = $repository->getRevenue( $donorId );
		$average   = $repository->getAverageRevenue( $donorId );
		$currency  = [
			'symbol'   => give_currency_symbol( give_get_currency(), true ),
			'position' => give_get_currency_position(),
		];

		if ( $donations && $count && $revenue && $average ) {
			return new WP_REST_Response(
				[
					'status'        => 200,
					'response'      => 'success',
					'body_response' => [
						[
							'donations' => $donations,
							'count'     => $count,
							'revenue'   => $revenue,
							'average'   => $average,
							'currency'  => $currency,
						],
					],
				]
			);
		}

		return new WP_REST_Response(
			[
				'status'        => 400,
				'response'      => 'database_error',
				'body_response' => [
					'message' => esc_html__( 'An error occurred while retrieving your donation records.', 'give' ),
				],
			]
		);
	}
}
