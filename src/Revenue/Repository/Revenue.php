<?php
namespace Give\Revenue\Repository;

use wpdb;

/**
 * Class Revenue
 * @package Give\Revenue\Repository
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
	 * @var string
	 */
	private $tableName;

	/**
	 * Revenue constructor
	 */
	public function constructor() {
		global $wpdb;

		$this->db        = $wpdb;
		$this->tableName = "{$this->db->prefix}give_revenue";
	}

	/**
	 *
	 */
	public function create() {
		return $this->db->insert(
			$this->tableName,
			[]
		);
	}
}
