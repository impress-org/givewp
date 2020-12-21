<?php

namespace Give\TestData\Addons\Funds;

/**
 * Class FundRepository
 * @package Give\TestData\Funds
 */
class FundRepository {
	/**
	 * @var FundFactory
	 */
	private $fundFactory;

	/**
	 * @param FundFactory $fundFactory
	 */
	public function __construct( FundFactory $fundFactory ) {
		$this->fundFactory = $fundFactory;
	}

	/** Insert fund
	 *
	 * @param array $fund
	 *
	 * @since 1.0.0
	 */
	public function insertFund( $fund ) {
		global $wpdb;

		// Set default fields
		$fund = wp_parse_args(
			apply_filters( 'give-test-data-fund-definition', $fund ),
			$this->fundFactory->definition()
		);

		// Insert fund
		$wpdb->insert( "{$wpdb->prefix}give_funds", $fund );

		do_action( 'give-test-data-insert-fund', $wpdb->insert_id, $fund );
	}
}
