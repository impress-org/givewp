<?php
namespace Give\Receipt\DetailGroup;

use Give\Receipt\Detail\Donation\Amount;
use Give\Receipt\Detail\Donation\PaymentGateway;
use Give\Receipt\Detail\Donation\Status;
use Give\Receipt\Detail\Donation\TotalAmount;
use Give\Receipt\DetailGroup;

class Donation extends DetailGroup {
	public $groupId = 'donationDetails';

	protected $detailsList = [
		Amount::class,
		PaymentGateway::class,
		Status::class,
		TotalAmount::class,
	];
}
