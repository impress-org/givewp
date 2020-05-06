<?php
namespace Give\Receipt\DonationDetailsGroup;

use Give\Receipt\DetailGroup;
use Give\Receipt\DonationDetailsGroup\Details\Amount;
use Give\Receipt\DonationDetailsGroup\Details\PaymentGateway;
use Give\Receipt\DonationDetailsGroup\Details\Status;
use Give\Receipt\DonationDetailsGroup\Details\TotalAmount;

/**
 * Class DonationDetailsGroup
 *
 * @since 2.7.0
 * @package Give\Receipt\DonationDetailsGroup
 */
class DonationDetailsGroup extends DetailGroup {
	/**
	 * @innheritDoc
	 */
	protected $detailsList = [
		PaymentGateway::class,
		Status::class,
		Amount::class,
		TotalAmount::class,
	];
}
