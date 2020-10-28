<?php

namespace Give\TestData;

use WP_CLI;

/**
 * A WP-CLI command for seeding test data.
 */
class SeedCommand {

	/**
	 * @param DonationFactory $factory
	 */
	public function __construct( Factory\DonationFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * @param $args
	 * @param array $assocArgs
	 */
	public function __invoke( $args, $assocArgs ) {
		$count         = WP_CLI\Utils\get_flag_value( $assocArgs, 'count', $default = 10 );
		$format        = WP_CLI\Utils\get_flag_value( $assocArgs, 'format', $default = 'table' );
		WP_CLI\Utils\format_items(
			$format,
			$donations = $this->factory->make( $count ),
			$keys      = array_keys( $this->factory->definition() )
		);
	}
}
