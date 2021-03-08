<?php

namespace Give\DonorProfiles\Tabs\DonationHistoryTab;

use WP_REST_Request;
use WP_REST_Response;
use Give\DonorProfiles\Tabs\Contracts\Route as RouteAbstract;
use Give\DonorProfiles\Repositories\Donations as DonationsRepository;

/**
 * @since 2.10.0
 */
class DonationsRoute extends RouteAbstract {

	/** @var string */
	public function endpoint() {
		return 'donations';
	}

	public function args() {
		return [];
	}

	/**
	 * @since 2.10.0
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return array
	 *
	 */
	public function handleRequest( $request ) {
		$donorId = give()->donorProfile->getId();

		$repository = new DonationsRepository();

		return $this->getData( $repository, $donorId );
	}

	/**
	 * @since 2.10.0
	 * @return array
	 *
	 */
	protected function getData( DonationsRepository $repository, $donorId ) {
		$donations = $repository->getDonations( $donorId );
		$count     = $repository->getDonationCount( $donorId );
		$revenue   = $repository->getRevenue( $donorId );
		$average   = $repository->getAverageRevenue( $donorId );

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
					'message' => __( 'An error occured while retrieving your donation records.', 'give' ),
				],
			]
		);
	}
}
