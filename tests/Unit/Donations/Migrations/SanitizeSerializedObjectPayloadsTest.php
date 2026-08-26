<?php

namespace Give\Tests\Unit\Donations\Migrations;

use Give\Donations\Migrations\SanitizeSerializedObjectPayloads;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class SanitizeSerializedObjectPayloadsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testRunRemovesObjectPayloadsFromUserMeta()
    {
        $userId = $this->factory()->user->create();
        update_user_meta($userId, 'last_name', serialize((object)['marker' => 'gadget']));

        (new SanitizeSerializedObjectPayloads())->run();

        $value = DB::table('usermeta')
            ->where('user_id', $userId)
            ->where('meta_key', 'last_name')
            ->getAll()[0]->meta_value;

        $this->assertSame('', maybe_unserialize($value));
    }

    /**
     * @since TBD
     */
    public function testRunKeepsLegitimateSerializedArraysInUserMeta()
    {
        $userId = $this->factory()->user->create();
        update_user_meta($userId, 'billing_address', serialize(['line1' => '123 Main St']));

        (new SanitizeSerializedObjectPayloads())->run();

        $value = DB::table('usermeta')
            ->where('user_id', $userId)
            ->where('meta_key', 'billing_address')
            ->getAll()[0]->meta_value;

        $this->assertSame(['line1' => '123 Main St'], maybe_unserialize($value));
    }

    /**
     * @since TBD
     */
    public function testRunRemovesObjectPayloadsFromDonorMeta()
    {
        $donorId = $this->createDonor();
        DB::table('give_donormeta')->insert([
            'donor_id'   => $donorId,
            'meta_key'   => '_give_donor_last_name',
            'meta_value' => serialize((object)['marker' => 'gadget']),
        ]);

        (new SanitizeSerializedObjectPayloads())->run();

        $value = DB::table('give_donormeta')
            ->where('donor_id', $donorId)
            ->where('meta_key', '_give_donor_last_name')
            ->getAll()[0]->meta_value;

        $this->assertSame('', $value);
    }

    /**
     * @since TBD
     */
    public function testRunRemovesNestedObjectPayloadsFromDonationMeta()
    {
        $donationId = $this->createDonation();
        $paymentMeta = [
            'user_info' => [
                'first_name' => 'Test',
                'last_name'  => serialize((object)['marker' => 'gadget']),
            ],
        ];

        DB::table('give_donationmeta')->insert([
            'donation_id' => $donationId,
            'meta_key'    => '_give_payment_meta',
            'meta_value'  => maybe_serialize($paymentMeta),
        ]);

        (new SanitizeSerializedObjectPayloads())->run();

        $value = DB::table('give_donationmeta')
            ->where('donation_id', $donationId)
            ->where('meta_key', '_give_payment_meta')
            ->getAll()[0]->meta_value;
        $unserialized = maybe_unserialize($value);

        $this->assertSame('Test', $unserialized['user_info']['first_name']);
        $this->assertSame('', $unserialized['user_info']['last_name']);
    }

    /**
     * @since TBD
     */
    public function testRunRemovesObjectPayloadsFromSessions()
    {
        $sessionValue = maybe_serialize([
            'give_purchase' => [
                'last_name' => serialize((object)['marker' => 'gadget']),
            ],
        ]);

        DB::table('give_sessions')->insert([
            'session_key'    => 'test-session',
            'session_value'  => $sessionValue,
            'session_expiry' => time() + DAY_IN_SECONDS,
        ]);

        (new SanitizeSerializedObjectPayloads())->run();

        $value = DB::table('give_sessions')
            ->where('session_key', 'test-session')
            ->getAll()[0]->session_value;
        $unserialized = maybe_unserialize($value);

        $this->assertSame('', $unserialized['give_purchase']['last_name']);
    }

    /**
     * @since TBD
     */
    public function testMigrationIdIsUnique()
    {
        $this->assertEquals('sanitize-serialized-object-payloads', SanitizeSerializedObjectPayloads::id());
    }

    /**
     * @since TBD
     */
    private function createDonor(): int
    {
        DB::table('give_donors')->insert([
            'user_id'        => 0,
            'email'          => 'donor@example.test',
            'name'           => 'Test Donor',
            'purchase_value' => '0.00',
            'purchase_count' => 0,
            'date_created'   => gmdate('Y-m-d H:i:s'),
        ]);

        return DB::last_insert_id();
    }

    /**
     * @since TBD
     */
    private function createDonation(): int
    {
        $postId = $this->factory()->post->create([
            'post_type'   => 'give_payment',
            'post_status' => 'publish',
            'post_title'  => 'Test Donation',
        ]);

        return $postId;
    }
}
