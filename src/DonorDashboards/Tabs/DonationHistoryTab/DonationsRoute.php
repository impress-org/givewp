<?php

namespace Give\DonorDashboards\Tabs\DonationHistoryTab;

use WP_REST_Request;
use WP_REST_Response;
use Give\Log\Log;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use Give\DonorDashboards\Repositories\Donations as DonationsRepository;

/**
 * @since 2.10.2
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
	 * @since 2.10.2
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 *
	 */
	public function handleRequest( WP_REST_Request $request ) {
		$donorId = give()->donorDashboard->getId();

		$repository = new DonationsRepository();

		return $this->getData( $repository, $donorId );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param  DonationsRepository  $repository
	 * @param $donorId
	 *
	 * @return WP_REST_Response
	 */
	protected function getData( DonationsRepository $repository, $donorId ) {

		// If the provided donor ID is valid, attempt to query data
		try {
			$donations = $repository->getDonations( $donorId );
			$count     = $repository->getDonationCount( $donorId );
			$revenue   = $repository->getRevenue( $donorId );
			$average   = $repository->getAverageRevenue( $donorId );
			$currency  = [
				'symbol'   => give_currency_symbol( give_get_currency(), true ),
				'position' => give_get_currency_position(),
			];

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
		} catch ( \Exception $e ) {
			Log::error(
				esc_html__( 'An error occurred while retrieving donation records', 'give' ),
				[
					'source'   => 'Donor Dashboard',
					'Donor ID' => $donorId,
					'Error'    => $e->getMessage(),
				]
			);

			return new WP_REST_Response(
				[
					'status'        => 400,
					'response'      => 'database_error',
					'body_response' => [
						'message' => esc_html__( 'An error occurred while retrieving your donation records. Contact the site administrator for assistance.', 'give' ),
					],
				]
			);
		}
	}
}
