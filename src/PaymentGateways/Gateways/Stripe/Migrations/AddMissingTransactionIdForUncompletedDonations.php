<?php

namespace Give\PaymentGateways\Gateways\Stripe\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 2.19.6
 */
class AddMissingTransactionIdForUncompletedDonations extends Migration
{

    /**
     * @inerhitDoc
     */
    public static function id()
    {
        return 'add-missing-transaction-id-for-uncompleted-stripe-donations';
    }

    /**
     * @inerhitDoc
     */
    public static function timestamp()
    {
        return strtotime('2022-03-28');
    }

    /**
     * @inerhitDoc
     */
    public function run()
    {
        $donationMetaTable = DB::prefix('give_donationmeta');
        $commentTable = DB::prefix('give_comments');
        $donationTable = DB::prefix('posts');

        /*
         * This SQL query will add transaction id to donation who pass following conditions:
         * - Donation status is other than "publish".
         * - Donation created after 20 March 2020. We released GiveWP 2.19.0 on 25th March 2022,
         *   So donation created after or on following date may not have transaction id.
         *   @see release information https://github.com/impress-org/givewp/releases/tag/2.19.0
         * - Donation process with Stripe payment method.
         * - Donation has note with "Stripe Charge/Payment Intent ID" prefix.
         * - Donation does not have transaction id.
         */
        DB::query(
            "
            INSERT INTO $donationMetaTable (donation_id, meta_key, meta_value)
            SELECT dm1.donation_id, '_give_payment_transaction_id',SUBSTR( gc.comment_content, 34 ) as transactionId
            FROM $donationMetaTable as dm1
                INNER JOIN $commentTable as gc on gc.comment_parent = dm1.donation_id
                INNER JOIN $donationTable as p on p.ID = dm1.donation_id
            WHERE NOT EXISTS (
                SELECT *
                FROM $donationMetaTable as dm2
                WHERE dm1.donation_id=dm2.donation_id
                    AND meta_key='_give_payment_transaction_id'
                )
                AND p.post_status!='publish'
                AND p.post_date > '2022-02-20'
                AND dm1.meta_key = '_give_payment_gateway'
                AND dm1.meta_value like '%stripe%'
                AND gc.comment_content like '%Stripe Charge/Payment Intent ID%'
                AND SUBSTR( gc.comment_content, 34 ) != ''
            ORDER BY dm1.donation_id DESC
            "
        );
    }

    /**
     * @inerhitDoc
     */
    public static function title()
    {
        return 'Add Missing Transaction Id For Uncompleted Stripe Donations';
    }
}
