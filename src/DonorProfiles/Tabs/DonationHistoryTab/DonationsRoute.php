<?php

namespace Give\DonorProfiles\Tabs\DonationHistoryTab;

use WP_REST_Request;
use Give\DonorProfiles\Tabs\Contracts\Route as RouteAbstract;
use Give\DonorProfiles\Repositories\Donations as DonationsRepository;
use Give\DonorProfiles\Helpers as DonorProfileHelpers;

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
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( $request ) {

		$donorId = give()->donorProfile->getId();

		$repository = new DonationsRepository();
		return $this->getData( $repository, $donorId );

	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getData( DonationsRepository $repository, $donorId ) {
		return [
			'donations' => $repository->getDonations( $donorId ),
			'count'     => $repository->getDonationCount( $donorId ),
			'revenue'   => $repository->getRevenue( $donorId ),
			'average'   => $repository->getAverageRevenue( $donorId ),
		];
	}
}
