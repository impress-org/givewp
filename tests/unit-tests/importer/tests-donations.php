<?php
/**
 * Meta
 * @package Give\Tests\Importer
 */


class WC_Tests_Give_Import_Donations extends Give_Unit_Test_Case {
	/**
	 * Test CSV file path.
	 *
	 * @var string
	 */
	protected $csv_file = '';

	/**
	 * Set it up.
	 */
	function setUp() {

		// check if import-donation file is include or not to check we are checking for a functions that is being declared in that file.
		$this->assertTrue( function_exists( 'give_save_import_donation_to_db' ) );

		// sample CSV file
		$this->csv_file = dirname( __FILE__ ) . '/sample.csv';

		parent::setUp();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Get CSV mapped items.
	 *
	 * @since 2.1
	 * @return array
	 */
	private function get_csv_mapped_items() {
		return array(
			'form_title',
			'amount',
			'currency',
			'form_level',
			'post_date',
			'first_name',
			'last_name',
			'company_name',
			'email',
			'user_id',
			'donor_id',
			'mode',
			'post_status',
			'gateway',
			'notes',
			'line1',
			'line2',
			'city',
			'zip',
			'state',
			'country',
		);
	}

	private function get_import_setting() {

		$file_dir = $this->csv_file;


		$raw_data = give_get_raw_data_from_file( $file_dir, 1, 15, ',' );

		return $import_setting = array(
			'delimiter'   => 'csv',
			'mode'        => 0,
			'create_user' => 1,
			'delete_csv'  => 1,
			'per_page'    => 25,
		);
	}

	/**
	 * Import donation in DB and test it's working fine or not
	 *
	 * @since 2.1
	 */
	public function test_give_save_import_donation_to_db() {
		$this->get_import_setting();
	}

}