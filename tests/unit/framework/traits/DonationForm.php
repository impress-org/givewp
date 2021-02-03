<?php

use Give\TestData\Factories\DonationFormFactory;
use Give\TestData\Repositories\DonationFormRepository;

trait DonationForm {

	protected function setupDonationForm() {
		$repository = give()->make( DonationFormRepository::class );
		$repository->insertDonationForm(
			give()->make( DonationFormFactory::class )->definition()
		);
	}
} 