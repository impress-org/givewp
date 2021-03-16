<?php

namespace Give\TestData\Commands;

use WP_CLI;
use Give\Log\LogFactory;
use Give\TestData\Factories\LogFactory as TestDataLogFactory;

/**
 * Class LogsSeedCommand
 * @package Give\TestData\Commands
 *
 * A WP-CLI command for seeding logs.
 */
class LogsSeedCommand {

	/**
	 * @var TestDataLogFactory
	 */
	private $testDataLogFactory;

	public function __construct( TestDataLogFactory $testDataLogFactory ) {
		$this->testDataLogFactory = $testDataLogFactory;
	}

	/**
	 * Generates GiveWP test logs
	 *
	 * [--count=<count>]
	 * : Number of logs to generate
	 * default: 10
	 *
	 * [--type=<type>]
	 * : Log type
	 * default: random
	 *
	 * [--category=<category>]
	 * : Log category
	 * default: Core
	 *
	 * [--source=<source>]
	 * : Log source
	 * default: Core
	 *
	 * [--preview=<preview>]
	 * : Preview generated data
	 * default: false
	 *
	 * ## EXAMPLES
	 *
	 *     wp give test-demonstration-page --preview=true
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assocArgs ) {
		$count    = WP_CLI\Utils\get_flag_value( $assocArgs, 'count', $default = 10 );
		$type     = WP_CLI\Utils\get_flag_value( $assocArgs, 'type', $default = 'random' );
		$category = WP_CLI\Utils\get_flag_value( $assocArgs, 'category', $default = 'Core' );
		$source   = WP_CLI\Utils\get_flag_value( $assocArgs, 'source', $default = 'Core' );
		$preview  = WP_CLI\Utils\get_flag_value( $assocArgs, 'preview', $default = false );

		$this->testDataLogFactory->setLogType( $type );
		$this->testDataLogFactory->setLogCategory( $category );
		$this->testDataLogFactory->setLogSource( $source );

		$logs = $this->testDataLogFactory->make( $count );

		if ( $preview ) {
			WP_CLI\Utils\format_items(
				'table',
				[ $logs ],
				array_keys( $this->testDataLogFactory->definition() )
			);
		} else {
			$progress = WP_CLI\Utils\make_progress_bar( 'Generating logs', 1 );

			foreach ( $logs as $data ) {
				$log = LogFactory::makeFromArray( $data );
				$log->save();

				$progress->tick();
			}

			$progress->finish();
		}
	}
}
