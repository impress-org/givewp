<?php
namespace Give\Receipt\DonationDetailsGroup;

use Give\Receipt\DetailGroup;
use Give\Receipt\DonationDetailsGroup\Details\Amount;
use Give\Receipt\DonationDetailsGroup\Details\PaymentGateway;
use Give\Receipt\DonationDetailsGroup\Details\Status;
use Give\Receipt\DonationDetailsGroup\Details\TotalAmount;

class DonationDetailsGroup extends DetailGroup {
	public $groupId = 'donationDetails';

	protected $detailsList = [
		PaymentGateway::class,
		Status::class,
		Amount::class,
		TotalAmount::class,
	];
}
