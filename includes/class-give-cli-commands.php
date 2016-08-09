<?php
/**
 * GIVE WP_CLI commands
 *
 * @package give
 * @since 1.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Add give command.
WP_CLI::add_command( 'give', 'GIVE_CLI_COMMAND' );


/**
 * Work with Give through WP-CLI
 *
 * Adds CLI support to Give through WP-CLI
 *
 * @since 1.7
 */
class GIVE_CLI_COMMAND {

    /**
     * @var int cache option counter.
     */
    private static $counter;


    /**
     * GIVE_CLI_Command constructor.
     */
    public function __construct() {}


    /**
     * Get Give details
     *
     * ## OPTIONS
     *
     * None. Returns basic info regarding your Give instance.
     *
     * ## EXAMPLES
     *
     * wp give details
     *
     * @since       1.7
     * @access		public
     *
     * @param		array $args
     * @param		array $assoc_args
     *
     * @return		void
     *
     * @subcommand  details
     */
    public function details( $args, $assoc_args ) {

        /**
         * Plugin Information
         */
        WP_CLI::log( $this->color_message( __( 'Give Version: ', 'give' ) ) . GIVE_VERSION  );


        /**
         * General Information.
         */
        WP_CLI::log( "\n####   " . $this->color_message( __( 'General information', 'give' ) ) . "   ####" );

        $success_page = give_get_option('success_page');
        $failure_page = give_get_option('failure_page');
        $history_page = give_get_option('history_page');

        WP_CLI::log( $this->color_message( sprintf(  __( 'Success Page: ', 'give' ) ) ) . ( $success_page ? "[{$success_page}] " . get_permalink( $success_page ) : __( 'Not Set', 'give' ) ) );
        WP_CLI::log( $this->color_message( __( 'Failed Transaction Page: ', 'give' ) ) . ( $failure_page ? "[{$failure_page}] " . get_permalink( $failure_page ) : __( 'Not Set', 'give' ) ) );
        WP_CLI::log( $this->color_message( __( 'Donation History Page: ', 'give' ) ) . ( $history_page ? "[{$history_page}] " . get_permalink( $history_page ) : __( 'Not Set', 'give' ) ) );
        WP_CLI::log( $this->color_message( __( 'Country: ', 'give' ) ) . give_get_country() );


        /**
         * Currency Information.
         */
        WP_CLI::log( "\n####   " . $this->color_message( __( 'Currency Information', 'give' ) ) . "   ####" );

        WP_CLI::log( $this->color_message( __( 'Currency: ', 'give' ), give_get_currency() ) );
        WP_CLI::log( $this->color_message( __( 'Currency Position: ', 'give' ), give_get_currency_position() ) );
        WP_CLI::log( $this->color_message( __( 'Thousand Separator: ', 'give' ), give_get_price_thousand_separator() ) );
        WP_CLI::log( $this->color_message( __( 'Decimal Separator: ', 'give' ), give_get_price_decimal_separator() ) );
        WP_CLI::log( $this->color_message( __( 'Number of Decimals: ', 'give' ), give_get_price_decimals() ) );


        /**
         * Payment gateways Information.
         */
        $gateways = give_get_ordered_payment_gateways( give_get_payment_gateways() );
        WP_CLI::log( $this->color_message( __( 'Enabled Gateways: ', 'give' ) ) );

        if( ! empty( $gateways ) ) {
            self::$counter = 1;
            foreach ( $gateways as $gateway ) {
                WP_CLI::log( '    ' . $this->color_message( self::$counter, '. ' ) . $gateway['admin_label'] );
                self::$counter++;
            }
        }else{
            WP_CLI::log( __( 'Not any payment gateways found', 'give' ) );
        }
    }


    /**
     * Get the forms currently posted on your Give site
     *
     * ## OPTIONS
     *
     * --id=<form_id>: A specific form ID to retrieve
     *
     * ## EXAMPLES
     *
     * wp give form 103
     *
     * @since       1.7
     * @access		public
     *
     * @param		array $args
     * @param		array $assoc_args
     * @return     void
     *
     * @subcommand form
     */
    public function form( $args, $assoc_args ) {
        // Bailout.
        if( empty( $args[0] ) ) {
            WP_CLI::warning( __( 'Form id is missing.', 'give' ) );
            return;
        }

        $form_id = absint( $args[0] );

        /* @var Give_Donate_Form $form */
        $form  = new Give_Donate_Form( $form_id );

        // Heading.
        WP_CLI::log( $this->color_message( sprintf( __( 'Report for form id %d', 'give' ), $form_id ) ) );

        // Form information.
        WP_CLI::log( $this->color_message( __( 'Form Title: ', 'give' ), $form->post_title ) );
        WP_CLI::log( $this->color_message( __( 'Income: ', 'give' ), give_currency_filter( $form->get_earnings() ) ) );
        WP_CLI::log( $this->color_message( __( 'Monthly Average Donations: ', 'give' ), give_currency_filter( give_get_average_monthly_form_sales( $form->ID ) ) ) );
        WP_CLI::log( $this->color_message( __( 'Monthly Average Income: ', 'give' ), give_currency_filter( give_get_average_monthly_form_earnings( $form->ID ) ) ) );
    }


    /**
     * Get the donors currently on your Give site. Can also be used to create donors records
     *
     * ## OPTIONS
     *
     * --id=<donor_id>: A specific donor ID to retrieve
     * --email=<donor_email>: The email address of the donor to retrieve
     * --create=<number>: The number of arbitrary donors to create. Leave as 1 or blank to create a donor with a specific email
     *
     * ## EXAMPLES
     *
     * wp give donors --id=103
     * wp give donors --email=john@test.com
     * wp give donors --create=1 --email=john@test.com
     * wp give donors --create=1 --email=john@test.com --name="John Doe"
     * wp give donors --create=1 --email=john@test.com --name="John Doe" user_id=1
     * wp give donors --create=1000
     *
     * @since       1.7
     * @access		public
     *
     * @param		array $args
     * @param		array $assoc_args
     *
     * @return      void
     *
     * @subcommand  donors
     */
    public function donors( $args, $assoc_args ) {}


    /**
     * Create sample donation data for your Give site
     *
     * ## OPTIONS
     *
     * --number: The number of purchases to create
     * --status=<status>: The status to create purchases as
     * --id=<product_id>: A specific product to create purchase data for
     * --price_id=<price_id>: A price ID of the specified product
     *
     * ## EXAMPLES
     *
     * wp give donations create --number=10 --status=completed
     * wp give donations create --number=10 --id=103
     *
     * @since       1.7
     * @access		public
     *
     * @param		array $args
     * @param		array $assoc_args
     *
     * @return      void
     *
     * @subcommand  donations
     *
     */
    public function donations( $args, $assoc_args ) {}

    /**
     * Get give plugin report.
     *
     * ## OPTIONS
     *
     * [<formID>]
     * : Donation form id, This can be used only if you want to see report for single form.
     *
     * [--type=<report_type>]
     * : Name of report type, value of this parameter can be [form].
     *
     *
     * @since 1.7
     *
     * @param string $args
     * @param array $assoc_args
     *
     * @subcommand report
     *
     * @return bool
     */
    public function report( $args, $assoc_args ){}


    /**
     * Delete cache (transient).
     *
     * ## OPTIONS
     *
     * [<formID>]
     * : Donation form id, This can be used only if you want to delete stat cache for single form.
     *
     * [--type=<stat_type>]
     * : Value of this parameter can be all (in case you want to delete all stat cache).
     *
     * ## EXAMPLES
     *
     *     # See form report
     *     wp give cache delete
     *
     * @since  1.7
     *
     * @param  string $args
     * @param  array $assoc_args
     *
     * @return void
     *
     * @subcommand cache
     */
    public function cache( $args, $assoc_args ){
        // Bailout.
        if( empty( $args[0] ) ) {
            WP_CLI::warning( __( 'Cache action is missing.', 'give' ) );
            return;
        }

        $action = $args[0];

        switch ( $action ) {
            case 'delete' :
                // Reset counter.
                self::$counter = 1;

                if( $this->delete_all_stats() ) {
                    // Report .eading.
                    WP_CLI::success( 'All form stat transient cache deleted.' );
                } else{
                    // Report .eading.
                    WP_CLI::warning( 'We did not find any transient to delete :)' );
                }
                break;
        }

    }

    /**
     * Delete all form stat transient
     *
     * @since  1.7
     * @access private
     */
    private function delete_all_stats() {
        global $wpdb;

        $stat_option_names = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
                '_transient_give_stats_'
            ),
            ARRAY_A
        );

        if( ! empty( $stat_option_names ) ) {

            // Convert transient option name to transient name.
            $stat_option_names = array_map(
                function( $option ){
                    return str_replace( '_transient_', '', $option['option_name'] );
                },
                $stat_option_names
            );

            foreach ( $stat_option_names as $option_name ) {
                if( delete_transient( $option_name ) ) {

                    WP_CLI::log( $this->color_message( self::$counter . '. ', $option_name ) );
                    self::$counter++;
                } else {
                    WP_CLI::log( $this->color_message( __( 'Error while deleting this transient: ', 'give' ), $option_name  ) );
                }
            }

            return true;
        }

        return false;
    }


    /**
     * Return colored message
     *
     * @param string $heading
     * @param string $message
     *
     * @return mixed
     */
    private function color_message( $heading, $message = '' ) {
        return WP_CLI::colorize( '%g' . $heading . '%n' ) . $message;
    }
}