<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 *
 * @covers AttachMeta
 */
final class AttachMetaTest extends TestCase
{
    /**
     * @since 2.19.0
     */
    public function testAttachMeta()
    {
        $builder = new QueryBuilder();

        $builder
            ->from(DB::raw('wp_posts'), 'posts')
            ->select(
                ['posts.ID', 'id'],
                ['posts.post_date', 'createdAt']
            )
            ->attachMeta(
                DB::raw('wp_give_donationmeta'),
                'posts.ID',
                'donation_id',
                ['_give_payment_total', 'amount']
            )
            ->leftJoin(DB::raw('wp_give_donationmeta'), 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', 1)
            ->orderBy('posts.post_date', 'DESC');

        $this->assertContains(
            "SELECT posts.ID AS id, posts.post_date AS createdAt, wp_give_donationmeta_attach_meta_amount.meta_value AS amount FROM wp_posts AS posts LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_amount ON posts.ID = wp_give_donationmeta_attach_meta_amount.donation_id AND wp_give_donationmeta_attach_meta_amount.meta_key = '_give_payment_total' LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id WHERE posts.post_type = 'give_payment' AND posts.post_status = 'give_subscription' AND donationMeta.meta_key = 'subscription_id' AND donationMeta.meta_value = '1' ORDER BY posts.post_date DESC",
            $builder->getSQL()
        );
    }

    /**
     * @since 2.19.6
     */
    public function testAttachMetaGroupConcatQuery()
    {
        $builder = new QueryBuilder();

        $builder
            ->from(DB::raw('wp_give_donors'))
            ->select(
                'id',
                'email',
                'name'
            )
            ->attachMeta(
                DB::raw('wp_give_donormeta'),
                'id',
                'donor_id',
                ['additional_email', 'additionalEmails', true]
            );

        $this->assertContains(
            "SELECT id, email, name, CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"',wp_give_donormeta_attach_meta_additionalEmails.meta_value,'\"')),']') AS additionalEmails FROM wp_give_donors LEFT JOIN wp_give_donormeta wp_give_donormeta_attach_meta_additionalEmails ON id = wp_give_donormeta_attach_meta_additionalEmails.donor_id AND wp_give_donormeta_attach_meta_additionalEmails.meta_key = 'additional_email' GROUP BY id",
            $builder->getSQL()
        );
    }

    /**
     * @since 2.19.0
     */
    public function testConfigureMeta()
    {
        $builder = new QueryBuilder();

        $builder
            ->from(DB::raw('wp_posts'), 'posts')
            ->select(
                ['posts.ID', 'id'],
                ['posts.post_date', 'createdAt']
            )
            ->configureMetaTable(
                DB::raw('wp_give_donationmeta'),
                'custom_meta_key',
                'custom_meta_value'
            )
            ->attachMeta(
                DB::raw('wp_give_donationmeta'),
                'posts.ID',
                'donation_id',
                ['_give_payment_total', 'amount']
            )
            ->leftJoin(DB::raw('wp_give_donationmeta'), 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.custom_meta_key', 'subscription_id')
            ->where('donationMeta.custom_meta_value', 1);

        $this->assertContains(
            "SELECT posts.ID AS id, posts.post_date AS createdAt, wp_give_donationmeta_attach_meta_amount.custom_meta_value AS amount FROM wp_posts AS posts LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_amount ON posts.ID = wp_give_donationmeta_attach_meta_amount.donation_id AND wp_give_donationmeta_attach_meta_amount.custom_meta_key = '_give_payment_total' LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id WHERE posts.post_type = 'give_payment' AND posts.post_status = 'give_subscription' AND donationMeta.custom_meta_key = 'subscription_id' AND donationMeta.custom_meta_value = '1'",
            $builder->getSQL()
        );
    }
}
