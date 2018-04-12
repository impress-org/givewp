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

	protected $importer_class = '';

	protected $raw_data = '';

	protected $raw_key = '';

	protected $import_setting = '';

	protected $total = '';

	/**
	 * Set it up.
	 */
	function setUp() {

		// check if import-donation file is include or not to check we are checking for a functions that is being declared in that file.
		$this->assertTrue( function_exists( 'give_save_import_donation_to_db' ) );

		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-donations.php';
		$this->assertTrue( class_exists( 'Give_Import_Donations' ) );


		$this->importer_class = Give_Import_Donations::get_instance();

		// sample CSV file
		$this->csv_file = dirname( __FILE__ ) . '/sample.csv';

		$this->raw_data = give_get_raw_data_from_file( $this->csv_file, 1, 25, ',' );

		$this->raw_key = give_get_raw_data_from_file( $this->csv_file, 0, 0, ',' );

		$this->import_setting = $this->get_import_setting();

		$this->total = $this->importer_class->get_csv_data_from_file_dir( $this->csv_file );

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

		return $import_setting = array(
			'delimiter'   => 'csv',
			'mode'        => 0,
			'create_user' => 1,
			'delete_csv'  => 0,
			'per_page'    => 25,
			'raw_key'     => $this->get_csv_mapped_items(),
		);
	}

	/**
	 * Get the total number of row from the CSV and count
	 * there are total 11 row 10 donation and 1st one is Donation key name
	 */
	public function test_get_csv_data_from_file_dir() {
		$this->assertEquals( 11, $this->total );
	}

	/**
	 * To test if dry run is working or not perfectly
	 *
	 * @since 2.1
	 */
	public function test_for_dry_run() {
		give_import_donation_report_reset();

		$import_setting = $this->import_setting;

		$raw_key = $import_setting['raw_key'];

		// data from CSV
		$raw_data = $this->raw_data;

		// donation meta key name
		$main_key = $this->main_key;

		// first add donation in dry run mode
		$import_setting['dry_run'] = 1;

		if ( ! empty( $import_setting['dry_run'] ) ) {
			$import_setting['csv_raw_data'] = $raw_data;

			$import_setting['donors_list'] = Give()->donors->get_donors( array(
				'number' => - 1,
				'fields' => array( 'id', 'user_id', 'email' ),
			) );
		}

		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			$payment_id                     = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}

		$dry_run_report = give_import_donation_report();

		$this->test_live_run();


		$live_run_report = give_import_donation_report();

		// compaired dry run and live run summery
		$this->assertEquals( true, serialize( $dry_run_report ) === serialize( $live_run_report ) );

		parent::tearDown();
	}

	public function test_live_run() {
		give_import_donation_report_reset();
		$import_setting = $this->get_import_setting();
		$raw_key        = $import_setting['raw_key'];

		$file_dir = $this->csv_file;

		// get the total number of rom from CSV
		$total = $this->importer_class->get_csv_data_from_file_dir( $file_dir );

		// get data from CSV
		$raw_data = give_get_raw_data_from_file( $file_dir, 1, $total, ',' );
		$main_key = give_get_raw_data_from_file( $file_dir, 0, 1, ',' );

		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			$payment_id                     = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}
	}

	/**
	 * Import donation in DB and test it's working fine or not
	 *
	 * @since 2.1
	 */
	public function test1_give_save_import_donation_to_db() {

		//$this->test_for_dry_run();


//		$import_setting = $this->get_import_setting();
//		$raw_key = $import_setting['raw_key'];
//
//		$file_dir = $this->csv_file;
//
//		// get the total number of rom from CSV
//		$total = $this->importer_class->get_csv_data_from_file_dir( $file_dir );
//
//		// get data from CSV
//		$raw_data = give_get_raw_data_from_file( $file_dir, 1, $total, ',' );
//		$main_key = give_get_raw_data_from_file( $file_dir, 0, 1, ',' );
//
//		$current_key = 1;
//		foreach ( $raw_data as $row_data ) {
//			$import_setting['donation_key'] = $current_key;
//			$payment_id = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
//			$current_key ++;
//			$this->assertTrue( class_exists( 'Give_Import_Donations' ) );
//		}
	}
}