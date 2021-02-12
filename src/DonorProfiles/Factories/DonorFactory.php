<?php

namespace Give\DonorProfiles\Factories;

use \Give_Donor as DonorModel;

class DonorFactory {

	public function make( $donorId ) {
		return new DonorModel( $donorId );
	}
}
