<?php

namespace Give\DonorProfiles\Factories;

use \Give_Donor as DonorModel;

class DonorFactory {

	public function make( int $donorId ) {
		return new DonorModel( $donorId );
	}
}
