<?php

namespace Give\DonationForms\Migrations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Remove duplicate meta keys and set correct value for form earnings
 *
 * @unreleased
 */
class RemoveDuplicateMeta extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'donation-forms-remove-duplicate-meta';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Remove duplicate meta';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2023-06-12');
    }

    /**
     * @unreleased
     */
    public function run()
    {
        $posts = DB::table('posts')
            ->select('ID')
            ->where('post_type', 'give_forms')
            ->getAll();

        foreach ($posts as $post) {
            $formEarnings = give_get_meta($post->ID, '_give_form_earnings');

            // Update meta with duplicate keys only
            if (count($formEarnings) > 1) {
                // Delete all meta
                give_delete_meta($post->ID, '_give_form_earnings');

                // Get earnings from donations
                $earnings = $this->getEarningsFromDonations($post->ID);
                give_update_meta($post->ID, '_give_form_earnings', $earnings);
            }
        }
    }

    /**
     * Get earnings from donations by Form ID
     *
     * @param $formId
     *
     * @return int
     */
    private function getEarningsFromDonations($formId): int
    {
        $donations = Db::table('posts')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                [DonationMetaKeys::FORM_ID(), 'formId'],
                [DonationMetaKeys::AMOUNT(), 'amount'])
            ->where('post_type', 'give_payment')
            ->where('give_donationmeta_attach_meta_formId.meta_value', $formId)
            ->getAll('ARRAY_A');

        $amounts = array_column($donations, 'amount');

        return array_sum($amounts);
    }

}
