<?php
namespace Give\Revenue\Repositories;

use wpdb;

/**
 * Class Revenue
 * @package Give\Revenue\Repositories
 *
 * Use this class to get data from "give_revenue" table.
 *
 * @since 2.9.0
 */
class Revenue {
	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Revenue constructor
	 */
	public function constructor() {
		global $wpdb;

		$this->db = $wpdb;
	}

	/**
	 *
	 */
	public function create() {
		return $this->db->insert(
			$this->db->give_revenue,
			[]
		);
	}
}
