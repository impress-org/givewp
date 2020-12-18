<?php

namespace Give\TestData\Repositories;

use Give\TestData\Factories\RevenueFactory as RevenueFactory;

/**
 * Class RevenueRepository
 * @package GiveTestData\TestData\Repositories
 */
class RevenueRepository {
	/**
	 * @var RevenueFactory
	 */
	private $revenueFactory;

	/**
	 * @param RevenueFactory $revenueFactory
	 */
	public function __construct( RevenueFactory $revenueFactory ) {
		$this->revenueFactory = $revenueFactory;
	}


	/**
	 * Insert revenue
	 *
	 * @param array $revenue
	 *
	 * @since 1.0.0
	 */
	public function insertRevenue( $revenue ) {
		global $wpdb;

		$revenue = wp_parse_args(
			apply_filters( 'give-test-data-revenue-definition', $revenue ),
			$this->revenueFactory->definition()
		);

		$wpdb->insert( "{$wpdb->prefix}give_revenue", $revenue );

		do_action( 'give-test-data-insert-revenue', $wpdb->insert_id, $revenue );
	}
}
