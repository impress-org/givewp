<?php

namespace Give\PaymentGateways\Gateways\Stripe\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
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
        $logTable = DB::prefix('give_comments');
        $donationTable = DB::prefix('posts');

        DB::query(
            "
            INSERT INTO $donationMetaTable (donation_id, meta_key, meta_value)
            SELECT dm1.donation_id, '_give_payment_transaction_id',SUBSTR( gc.comment_content, 34 ) as transactionId
            FROM $donationMetaTable as dm1
                INNER JOIN $logTable as gc on gc.comment_parent = dm1.donation_id
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
