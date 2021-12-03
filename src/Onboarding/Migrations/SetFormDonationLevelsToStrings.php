<?php

namespace Give\Onboarding\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Onboarding\FormRepository;

/**
 * This resolves an issue where the donation level data for the form created during onboarding was serialized as
 * integers instead of strings, causing issues throughout
 *
 * @since 2.13.4 preserve additional donation level data
 * @since 2.13.3
 */
class SetFormDonationLevelsToStrings extends Migration
{
    /**
     * @var FormRepository
     */
    private $formRepository;

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

    public function __construct(FormRepository $formRepository)
    {
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $formId = $this->formRepository->getDefaultFormID();

        if (empty($formId)) {
            return;
        }

        $donationLevels = give_get_meta($formId, '_give_donation_levels', true);

        foreach ($donationLevels as &$level) {
            $level['_give_id']['level_id'] = (string)$level['_give_id']['level_id'];
            $level['_give_amount'] = give_sanitize_amount_for_db($level['_give_amount']);
        }
        unset($level);

        update_post_meta($formId, '_give_donation_levels', $donationLevels);
    }
}
