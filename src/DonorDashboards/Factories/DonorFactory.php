<?php

namespace Give\DonorDashboards\Factories;

use \Give_Donor as DonorModel;

/**
 * @since 2.10.0
 */
class DonorFactory {

	public function make( $donorId ) {
		return new DonorModel( $donorId );
	}
}
