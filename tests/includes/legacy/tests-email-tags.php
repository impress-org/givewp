<?php

/**
 * @group email_tags
 */
class Tests_Email_Tags extends Give_Unit_Test_Case {
	public function setUp() {

        $current_user = new WP_User( 1 );
        $current_user->set_role( 'administrator' );
        wp_update_user(
            array(
                'ID'         => 1,
                'first_name' => 'Admin',
                'last_name'  => 'User',
            )
        );

        global $wpdb;
        $wpdb->update("{$wpdb->prefix}give_donors", [ 'name' => 'Admin User' ], [ 'user_id' => 1 ] );

        parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test function give_email_tag_first_name
	 *
	 * @since 2.0
	 * @cover give_email_tag_first_name
	 */
	function test_give_email_tag_first_name() {
        /*
         * Case 1: First name from payment.
         */
        $payment_id = Give_Helper_Payment::create_simple_payment();
        $donor_id = give_get_payment_donor_id($payment_id);
        $firstname = give_email_tag_first_name(array('payment_id' => $payment_id));

        $this->assertEquals('Admin', $firstname);

        /*
         * Case 2: First name from user_id.
         */
        $firstname = give_email_tag_first_name(array('user_id' => 1));
        $this->assertEquals('Admin', $firstname);

        /*
         * Case 3: First name from donor_id.
         */
        $firstname = give_email_tag_first_name(array('donor_id' => $donor_id));
        $this->assertEquals('Admin', $firstname);

        /*
         * Case 4: First name with filter
         */
        add_filter('give_email_tag_first_name', array($this, 'give_first_name'), 10, 2);

        $firstname = give_email_tag_first_name(array('donor_id' => $donor_id));
        $this->assertEquals('Give', $firstname);

        remove_filter('give_email_tag_first_name', array($this, 'give_first_name'), 10);
    }

    /**
     * Add give_email_tag_first_name filter to give_email_tag_first_name function.
     *
     * @since 2.0
     *
     * @param  string  $firstname
     * @param  array  $tag_args
     *
     * @return string
     */
    public function give_first_name($firstname, $tag_args)
    {
        if (array_key_exists('donor_id', $tag_args)) {
            $firstname = 'Give';
        }

        return $firstname;
    }

    /**
     * Test function give_email_tag_fullname
     *
     * @since 2.0
     * @cover give_email_tag_fullname
     */
    function test_give_email_tag_fullname()
    {
        /*
         * Case 1: Full name from payment.
         */
        $payment_id = Give_Helper_Payment::create_simple_payment();
        $fullname = give_email_tag_fullname(array('payment_id' => $payment_id));

        $this->assertEquals('Admin User', $fullname);

        /*
         * Case 2: Full name from user_id.
         */
        $fullname = give_email_tag_fullname(array('user_id' => 1));

        $this->assertEquals('Admin User', $fullname);

        /*
         * Case 3: Full name with filter
         */
        add_filter('give_email_tag_fullname', array($this, 'give_fullname'), 10, 2);

        $fullname = give_email_tag_fullname(array('donor_id' => 1));
        $this->assertEquals('Give WP', $fullname);

        remove_filter('give_email_tag_fullname', array($this, 'give_fullname'), 10);
    }

	/**
	 * Add give_email_tag_fullname filter to give_email_tag_fullname function.
	 *
	 * @since 2.0
	 *
	 * @param string $fullname
	 * @param array  $tag_args
	 *
     * @return string
     */
    public function give_fullname($fullname, $tag_args)
    {
        if (array_key_exists('donor_id', $tag_args)) {
            $fullname = 'Give WP';
        }

        return $fullname;
    }

    /**
     * Test function give_email_tag_username
     *
     * @since 2.0
     * @cover give_email_tag_username
     */
    function test_give_email_tag_username()
    {
        /*
         * Case 1: User name from payment.
         */
        $payment_id = Give_Helper_Payment::create_simple_payment();
        $donor_id = give_get_payment_donor_id($payment_id);
        $username = give_email_tag_username(array('payment_id' => $payment_id));

        $this->assertEquals('admin', $username);

        /*
         * Case 2: User name from user_id.
         */
        $username = give_email_tag_username(array('user_id' => 1));
        $this->assertEquals('admin', $username);

        /*
         * Case 3: User name with filter
         */
        add_filter('give_email_tag_username', array($this, 'give_username'), 10, 2);

        $username = give_email_tag_username(array('donor_id' => $donor_id));
        $this->assertEquals('give', $username);

        remove_filter('give_email_tag_username', array($this, 'give_username'), 10);
    }

	/**
	 * Add give_email_tag_username filter to give_email_tag_username function.
	 *
	 * @since 2.0
	 *
	 * @param string $username
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_username( $username, $tag_args ) {
		if ( array_key_exists( 'donor_id', $tag_args ) ) {
			$username = 'give';
		}

        return $username;
    }

    /**
     * Test function give_email_tag_user_email
     *
     * @since 2.0
     * @cover give_email_tag_user_email
     */
    function test_give_email_tag_user_email()
    {
        /*
         * Case 1: User email from payment.
         */
        $payment_id = Give_Helper_Payment::create_simple_payment();
        $donor_id = give_get_payment_donor_id($payment_id);
        $user_email = give_email_tag_user_email(array('payment_id' => $payment_id));

        $this->assertEquals('admin@example.org', $user_email);

        /*
         * Case 2: User email from user_id.
         */
        $user_email = give_email_tag_user_email(array('user_id' => 1));
        $this->assertEquals('admin@example.org', $user_email);

        /*
         * Case 3: User email with filter
         */
        add_filter('give_email_tag_user_email', array($this, 'give_user_email'), 10, 2);

        $user_email = give_email_tag_user_email(array('donor_id' => 1));
        $this->assertEquals('give@givewp.com', $user_email);

        remove_filter('give_email_tag_user_email', array($this, 'give_user_email'), 10);
    }

	/**
	 * Add give_email_tag_user_email filter to give_email_tag_user_email function.
	 *
	 * @since 2.0
	 *
	 * @param string $user_email
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_user_email( $user_email, $tag_args ) {
		if ( array_key_exists( 'donor_id', $tag_args ) ) {
			$user_email = 'give@givewp.com';
		}

		return $user_email;
	}

	/**
	 * Test function give_email_tag_billing_address
	 *
	 * @since 2.0
	 * @cover give_email_tag_billing_address
	 */
	function test_give_email_tag_billing_address()
    {
        /*
         * Case 1: Billing Address from payment.
         */
        $payment_id = Give_Helper_Payment::create_simple_payment();
        $donor_id = give_get_payment_donor_id($payment_id);
        $billing_address = give_email_tag_billing_address(array('payment_id' => $payment_id));

        $this->assertEquals(',', trim(str_replace("\n", '', $billing_address)));

        /*
         * Case 2: Billing Address with filter
         */
        add_filter('give_email_tag_billing_address', array($this, 'give_billing_address'), 10, 2);

        $billing_address = give_email_tag_billing_address(array('user_id' => 1));
        $this->assertEquals('San Diego, CA', $billing_address);

		remove_filter( 'give_email_tag_billing_address', array( $this, 'give_billing_address' ), 10 );
	}

	/**
	 * Add give_email_tag_billing_address filter to give_email_tag_billing_address function.
	 *
	 * @since 2.0
	 *
	 * @param string $billing_address
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_billing_address( $billing_address, $tag_args ) {
		if ( array_key_exists( 'user_id', $tag_args ) ) {
			$billing_address = 'San Diego, CA';
		}

		return $billing_address;
	}

	/**
	 * Test function give_email_tag_date
	 *
	 * @since 2.0
	 * @cover give_email_tag_date
	 */
	function test_give_email_tag_date() {
		/*
		 * Case 1: Date from payment.
		 */
		$payment_id = Give_Helper_Payment::create_simple_payment();
		$date       = give_email_tag_date( array( 'payment_id' => $payment_id ) );

		$this->assertEquals( date( 'F j, Y', current_time( 'timestamp' ) ), $date );

		/*
		 * Case 2: Date with filter
		 */
		add_filter( 'give_email_tag_date', array( $this, 'give_date' ), 10, 2 );

		$date = give_email_tag_date( array( 'user_id' => 1 ) );
		$this->assertEquals( 'December 7, 2014', $date );

		remove_filter( 'give_email_tag_date', array( $this, 'give_date' ), 10 );
	}

	/**
	 * Add give_email_tag_date filter to give_email_tag_date function.
	 *
	 * @since 2.0
	 *
	 * @param string $date
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_date( $date, $tag_args ) {
		if ( array_key_exists( 'user_id', $tag_args ) ) {
			$date = 'December 7, 2014';
		}

		return $date;
	}

	/**
	 * Test function give_email_tag_amount
	 *
	 * @since 2.0
	 * @cover give_email_tag_amount
	 * @cover give_email_tag_price
	 */
	function test_give_email_tag_amount() {
		/*
		 * Case 1: Amount from payment.
		 */
		$payment_id = Give_Helper_Payment::create_simple_payment();
		$amount     = give_email_tag_amount( array( 'payment_id' => $payment_id ) );

		$this->assertEquals( '$20.00', htmlentities( $amount, ENT_COMPAT, 'UTF-8' ) );

		/*
		 * Case 2: Amount with filter
		 */
		add_filter( 'give_email_tag_amount', array( $this, 'give_amount' ), 10, 2 );

		$amount = give_email_tag_amount( array( 'user_id' => 1 ) );
		$this->assertEquals( '$30.00', $amount );

		remove_filter( 'give_email_tag_amount', array( $this, 'give_amount' ), 10 );
	}

	/**
	 * Add give_email_tag_amount filter to give_email_tag_amount function.
	 *
	 * @since 2.0
	 *
	 * @param string $amount
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_amount( $amount, $tag_args ) {
		if ( array_key_exists( 'user_id', $tag_args ) ) {
			$amount = '$30.00';
		}

		return $amount;
	}

	/**
	 * Test function give_email_tag_payment_id
	 *
	 * @since 2.0
	 * @cover give_email_tag_payment_id
	 */
	function test_give_email_tag_payment_id() {
		give_update_option( 'sequential-ordering_status', 'disabled' );

		/*
		 * Case 1: Payment ID from payment without sequential feature.
		 */
		$expected_payment_id = Give_Helper_Payment::create_simple_payment();
		$actual_payment_id   = give_email_tag_payment_id( array( 'payment_id' => $expected_payment_id ) );

		$this->assertEquals( $expected_payment_id, $actual_payment_id );

		/*
		 * Case 2: Payment ID with filter and without sequential feature.
		 */
		add_filter( 'give_email_tag_payment_id', array( $this, 'give_payment_id' ), 10, 2 );

		$actual_payment_id = give_email_tag_payment_id( array( 'user_id' => 1 ) );
		$this->assertEquals( 'GIVE-1 [Pending]', $actual_payment_id );

		remove_filter( 'give_email_tag_payment_id', array( $this, 'give_payment_id' ), 10 );

		give_update_option( 'sequential-ordering_status', 'enabled' );

		/*
		 * Case 3: Payment ID from payment.
		 */
		$expected_payment_id = Give_Helper_Payment::create_simple_payment();
		$actual_payment_id   = give_email_tag_payment_id( array( 'payment_id' => $expected_payment_id ) );

		$this->assertEquals( Give()->seq_donation_number->get_serial_code( $expected_payment_id ), $actual_payment_id );
	}

	/**
	 * Add give_email_tag_payment_id filter to give_email_tag_payment_id function.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_id
	 * @param array  $tag_args
	 *
	 * @return string
	 */
	public function give_payment_id( $payment_id, $tag_args ) {
		if ( array_key_exists( 'user_id', $tag_args ) ) {
			$payment_id = 'GIVE-1 [Pending]';
		}

		return $payment_id;
	}

	/**
	 * Test function give_email_tag_donation
	 *
	 * @since 2.0
	 * @cover give_email_tag_donation
	 */
	function test_give_email_tag_donation() {
		/*
		 * Case 1: Donation form title from simple donation.
		 */
		$donation            = Give_Helper_Payment::create_simple_payment();
		$donation_form_title = give_email_tag_donation( array( 'payment_id' => $donation ) );

		$this->assertEquals( 'Test Donation Form', $donation_form_title );

		/*
		 * Case 2: Donation form title from multi type donation.
		 */
		$donation            = Give_Helper_Payment::create_multilevel_payment();
		$donation_form_title = give_email_tag_donation( array( 'payment_id' => $donation ) );

		$this->assertEquals( 'Multi-level Test Donation Form - Mid-size Gift', $donation_form_title );

		/*
		 * Case 3: Donation form title with filter
		 */
		add_filter( 'give_email_tag_donation', array( $this, 'give_donation' ) );
		$donation_form_title = give_email_tag_donation( array( 'payment_id' => $donation ) );
		$this->assertEquals( 'GIVE', $donation_form_title );
		remove_filter( 'give_email_tag_donation', array( $this, 'give_donation' ), 10 );
	}

	/**
	 * Add give_email_tag_donation filter to give_email_tag_donation function.
	 *
	 * @since 2.0
	 *
	 * @param string $donation_form_title
	 *
	 * @return string
	 */
	public function give_donation( $donation_form_title ) {
		$donation_form_title = 'GIVE';

		return $donation_form_title;
	}

	/**
	 * Test function give_email_tag_form_title
	 *
	 * @since 2.0
	 * @cover give_email_tag_form_title
	 */
	function test_give_email_tag_form_title() {
		/*
		 * Case 1: Form title from simple form_title.
		 */
		$payment    = Give_Helper_Payment::create_simple_payment();
		$form_title = give_email_tag_form_title( array( 'payment_id' => $payment ) );

		$this->assertEquals( 'Test Donation Form', $form_title );

		/*
		 * Case 2: Form title from multi type form_title.
		 */
		$payment    = Give_Helper_Payment::create_multilevel_payment();
		$form_title = give_email_tag_form_title( array( 'payment_id' => $payment ) );

		$this->assertEquals( 'Multi-level Test Donation Form', $form_title );

		/*
		 * Case 3: Form title with filter
		 */
		add_filter( 'give_email_tag_form_title', array( $this, 'give_form_title' ) );
		$form_title = give_email_tag_form_title( array( 'payment_id' => $payment ) );
		$this->assertEquals( 'GIVE', $form_title );
		remove_filter( 'give_email_tag_form_title', array( $this, 'give_form_title' ), 10 );
	}

	/**
	 * Add give_email_tag_form_title filter to give_email_tag_form_title function.
	 *
	 * @since 2.0
	 *
	 * @param string $form_title
	 *
	 * @return string
	 */
	public function give_form_title( $form_title ) {
		$form_title = 'GIVE';

		return $form_title;
	}

	/**
	 * Test function give_email_tag_payment_method
	 *
	 * @since 2.0
	 * @cover give_email_tag_payment_method
	 */
	function test_give_email_tag_payment_method() {
		/*
		 * Case 1: Payment method from simple payment_method.
		 */
		$payment        = Give_Helper_Payment::create_simple_payment();
		$payment_method = give_email_tag_payment_method( array( 'payment_id' => $payment ) );

		$this->assertEquals( 'Test Donation', $payment_method );

		/*
		 * Case 2: Payment method with filter
		 */
		add_filter( 'give_email_tag_payment_method', array( $this, 'give_payment_method' ) );
		$payment_method = give_email_tag_payment_method( array( 'payment_id' => $payment ) );
		$this->assertEquals( 'Manual', $payment_method );
		remove_filter( 'give_email_tag_payment_method', array( $this, 'give_payment_method' ), 10 );
	}

	/**
	 * Add give_email_tag_payment_method filter to give_email_tag_payment_method function.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_method
	 *
	 * @return string
	 */
	public function give_payment_method( $payment_method ) {
		$payment_method = 'Manual';

		return $payment_method;
	}

	/**
	 * Test function give_email_tag_payment_total
	 *
	 * @since 2.0
	 * @cover give_email_tag_payment_total
	 */
	function test_give_email_tag_payment_total() {
		/*
		 * Case 1: Payment total from simple payment_total.
		 */
		$payment       = Give_Helper_Payment::create_simple_payment();
		$payment_total = give_email_tag_payment_total( array( 'payment_id' => $payment ) );
		$payment_total = html_entity_decode( $payment_total, ENT_COMPAT, 'UTF-8' );

		$this->assertEquals( '$20.00', htmlentities( $payment_total, ENT_COMPAT, 'UTF-8' ) );

		/*
		 * Case 2: Payment total with filter
		 */
		add_filter( 'give_email_tag_payment_total', array( $this, 'give_payment_total' ) );
		$payment_total = give_email_tag_payment_total( array( 'payment_id' => $payment ) );
		$payment_total = html_entity_decode( $payment_total, ENT_COMPAT, 'UTF-8' );

		$this->assertEquals( '$30', htmlentities( $payment_total, ENT_COMPAT, 'UTF-8' ) );
		remove_filter( 'give_email_tag_payment_total', array( $this, 'give_payment_total' ), 10 );
	}

	/**
	 * Add give_email_tag_payment_total filter to give_email_tag_payment_total function.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_total
	 *
	 * @return string
	 */
	public function give_payment_total( $payment_total ) {
		$payment_total = give_currency_filter( 30 );

		return $payment_total;
	}

	/**
	 * Test function give_email_tag_sitename
	 *
	 * @since 2.0
	 * @cover give_email_tag_sitename
	 */
	function test_give_email_tag_sitename() {
		/*
		 * Case 1: From WordPress function.
		 */
		$sitename = give_email_tag_sitename();

		$this->assertEquals( 'Test Blog', $sitename );

		/*
		 * Case 2: With filter
		 */
		add_filter( 'give_email_tag_sitename', array( $this, 'give_sitename' ) );
		$sitename = give_email_tag_sitename();
		$this->assertEquals( 'Test Blog | Give', $sitename );
		remove_filter( 'give_email_tag_sitename', array( $this, 'give_sitename' ), 10 );
	}

	/**
	 * Add give_email_tag_sitename filter to give_email_tag_sitename function.
	 *
	 * @since 2.0
	 *
	 * @param string $sitename
	 *
	 * @return string
	 */
	public function give_sitename( $sitename ) {
		$sitename = 'Test Blog | Give';

		return $sitename;
	}

	/**
	 * Test function give_email_tag_receipt_link_url
	 *
	 * @since 2.0
	 * @cover give_email_tag_receipt_link_url
	 */
	function test_give_email_tag_receipt_link_url() {
		$payment = Give_Helper_Payment::create_simple_payment();

		$receipt_link_url = give_email_tag_receipt_link_url(
			array(
				'payment_id' => $payment,
			)
		);

		$this->assertRegExp(
			'/action=view_in_browser/',
			$receipt_link_url
		);

		$this->assertRegExp(
			'/_give_hash=/',
			$receipt_link_url
		);
	}

	/**
	 * Test function give_email_tag_receipt_link
	 *
	 * @since 2.0
	 * @cover give_email_tag_receipt_link
	 */
	function test_give_email_tag_receipt_link() {
		$payment = Give_Helper_Payment::create_simple_payment();

		$receipt_link = give_email_tag_receipt_link(
			array(
				'payment_id' => $payment,
			)
		);

		$this->assertRegExp(
			'/>View the receipt in your browser &raquo;<\/a>/',
			$receipt_link
		);

		$this->assertRegExp(
			'/<a href=".+?\?action=view_in_browser/',
			$receipt_link
		);

		$this->assertRegExp(
			'/_give_hash=/',
			$receipt_link
		);

	}


	/**
	 * Test function give_email_tag_donation_history_link
	 *
	 * @since 2.0
	 * @cover give_email_tag_donation_history_link
	 */
	function test_give_email_tag_donation_history_link() {
		// Create new table columns manually.
		// Are db columns setup?
		if ( ! Give()->donors->does_column_exist( 'token' ) ) {
			Give()->email_access->create_columns();
		}

		Give_Helper_Payment::create_simple_payment();

		$link = give_email_tag_donation_history_link( array( 'user_id' => 1 ) );

		$this->assertRegExp(
			'/target="_blank">View your donation history &raquo;<\/a>/',
			$link
		);

		$this->assertRegExp(
			'/<a href=".+?\?give_nl=/',
			$link
		);

		$link = give_email_tag_donation_history_link(
			array(
				'user_id'            => 1,
				'email_content_type' => 'text/plain',
			)
		);

		$this->assertRegExp(
			'/View your donation history: .+?\?give_nl=/',
			$link
		);

	}

	/**
	 * Test meta data email tag
	 *
	 * Note: this tag render donor, donation and form dynamic dynamically
	 *
	 * @since 2.1.0
	 */
	function test_give_email_tag_metadata() {
		$payment_id = Give_Helper_Payment::create_simple_payment();
		$donor_id   = give_get_payment_donor_id( $payment_id );

		/*
		 * Case 1: donor meta data tests.
		 */
		$donor_tag_args = array( 'donor_id' => $donor_id );
		$this->assertEquals( 'Admin', __give_render_metadata_email_tag( '{meta_donor__give_donor_first_name}', $donor_tag_args ) );
		$this->assertEquals( 'User', __give_render_metadata_email_tag( '{meta_donor__give_donor_last_name}', $donor_tag_args ) );

		Give()->donor_meta->update_meta( $donor_id, '_give_stripe_customer_id', 2 );

		$this->assertEquals( 2, __give_render_metadata_email_tag( '{meta_donor__give_stripe_customer_id}', $donor_tag_args ) );
		$this->assertEquals( 1, __give_render_metadata_email_tag( '{meta_donor_id}', $donor_tag_args ) );
		$this->assertEquals( 1, __give_render_metadata_email_tag( '{meta_donor_user_id}', $donor_tag_args ) );
		$this->assertEquals( 'Admin User', __give_render_metadata_email_tag( '{meta_donor_name}', $donor_tag_args ) );
		$this->assertEquals( 'admin@example.org', __give_render_metadata_email_tag( '{meta_donor_email}', $donor_tag_args ) );

		$this->assertEquals( 'Admin User', __give_render_metadata_email_tag( '{meta_donor_name}', array( 'user_id' => 1 ) ) );
		$this->assertEquals( 'Admin User', __give_render_metadata_email_tag( '{meta_donor_name}', array( 'payment_id' => $payment_id ) ) );

		/*
		 * Case 2: donation meta data tests.
		 */

		/*
		 * Case 3: donation form meta data tests.
		*/
	}
}
