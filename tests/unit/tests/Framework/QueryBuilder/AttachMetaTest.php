<?php

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class AttachMetaTest extends TestCase
{
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
            "SELECT posts.ID AS id, posts.post_date AS createdAt, wp_give_donationmeta_attach_meta_0.meta_value AS amount FROM wp_posts AS posts LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_0 ON posts.ID = wp_give_donationmeta_attach_meta_0.donation_id AND wp_give_donationmeta_attach_meta_0.meta_key = '_give_payment_total' LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id WHERE posts.post_type = 'give_payment' AND posts.post_status = 'give_subscription' AND donationMeta.meta_key = 'subscription_id' AND donationMeta.meta_value = '1' ORDER BY posts.post_date DESC",
            $builder->getSQL()
        );
    }


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
            "SELECT posts.ID AS id, posts.post_date AS createdAt, wp_give_donationmeta_attach_meta_0.custom_meta_value AS amount FROM wp_posts AS posts LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_0 ON posts.ID = wp_give_donationmeta_attach_meta_0.donation_id AND wp_give_donationmeta_attach_meta_0.custom_meta_key = '_give_payment_total' LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id WHERE posts.post_type = 'give_payment' AND posts.post_status = 'give_subscription' AND donationMeta.custom_meta_key = 'subscription_id' AND donationMeta.custom_meta_value = '1'",
            $builder->getSQL()
        );
    }
}
