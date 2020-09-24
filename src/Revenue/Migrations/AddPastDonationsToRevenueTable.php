<?php
namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class AddPastDonationToRevenueTable
 *
 * use this table to migrated past donations data to revenue table.
 *
 * @package Give\Revenue\Migrations
 *
 * @since 2.9.0
 */
class AddPastDonationsToRevenueTable extends Migration {

	/**
	 * @inheritdoc
	 */
	public function run() {}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'add-past-donation-data-to-revenue-table';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2019-09-24' );
	}
}
