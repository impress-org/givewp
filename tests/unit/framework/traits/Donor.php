<?php

use Give\TestData\Factories\DonorFactory;
use Give\TestData\Repositories\DonorRepository;

trait Donor {

	protected function setupDonor() {
		$repository = give()->make( DonorRepository::class );
		$repository->insertDonor(
			give()->make( DonorFactory::class )->definition()
		);
	}
} 