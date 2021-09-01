<?php

namespace Give\Onboarding\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Onboarding\FormRepository;

/**
 * This resolves an issue where the donation level data for the form created during onboarding was serialized as
 * integers instead of strings, causing issues throughout
 *
 * @since 2.13.3
 */
class SetFormDonationLevelsToStrings extends Migration
{
	/**
	 * @inheritDoc
	 */
	public static function id()
	{
		return 'set-form-donation-levels-to-strings';
	}

	/**
	 * @inheritDoc
	 */
	public static function timestamp()
	{
		return strtotime('2020-09-01 11:47:00');
	}

	/**
	 * @var FormRepository
	 */
	private $formRepository;

	public function __construct(FormRepository $formRepository)
	{
		$this->formRepository = $formRepository;
	}

	public function run()
	{
		$formId = $this->formRepository->getDefaultFormID();

		if (empty($formId)) {
			return;
		}

		$donationLevels = give_get_meta($formId, '_give_donation_levels', true);

		$updatedLevels = [];
		foreach ($donationLevels as $level) {
			$updatedLevels[] = [
				'_give_id' => [
					'level_id' => (string)$level['_give_id']['level_id'],
				],
				'_give_amount' => give_sanitize_amount_for_db($level['_give_amount']),
			];
		}

		update_post_meta($formId, '_give_donation_levels', $updatedLevels);
	}
}
