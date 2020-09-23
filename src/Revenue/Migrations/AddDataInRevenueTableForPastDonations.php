<?php
namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class AddDataInRevenueTableForPastDonations
 * @package Give\Revenue\Migrations
 *
 * @since 2.9.0
 */
class AddDataInRevenueTableForPastDonations extends Migration {
	/**
	 * @inheritdoc
	 */
	public function run() {}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'add-data-to-revenue-table-for-past-donations';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2020-09-23' );
	}
}
