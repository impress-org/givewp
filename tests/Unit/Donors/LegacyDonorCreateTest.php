<?php

namespace Give\Tests\Unit\Donors;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor;

/**
 * Covers what Give_Donor::create() stores.
 *
 * @since TBD
 */
class LegacyDonorCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testCreateStoresAnArrayOfPaymentIdsAsAList()
    {
        $id = (new Give_Donor())->create([
            'name' => 'Ann Array',
            'email' => 'ann-array@example.com',
            'payment_ids' => [20, 21, 20],
        ]);

        $this->assertNotFalse($id);
        $this->assertSame('20,21', (new Give_Donor($id))->payment_ids);
    }

    /**
     * @since TBD
     */
    public function testCreateAddsPaymentIdsToAnExistingDonorWithoutClearingThem()
    {
        $first = (new Give_Donor())->create([
            'name' => 'Ann Existing',
            'email' => 'ann-existing@example.com',
            'payment_ids' => '10',
        ]);

        $second = (new Give_Donor())->create([
            'name' => 'Ann Existing',
            'email' => 'ann-existing@example.com',
            'payment_ids' => [20, 21],
        ]);

        $this->assertSame($first, $second);

        $paymentIds = explode(',', (new Give_Donor($first))->payment_ids);
        sort($paymentIds);

        $this->assertSame(['10', '20', '21'], $paymentIds);
    }

    /**
     * @since TBD
     */
    public function testCreateStoresTheSanitizedValues()
    {
        $id = (new Give_Donor())->create([
            'name' => '<script>alert(1)</script><b>Ann</b> Sanitized',
            'email' => 'ann-sanitized@example.com',
        ]);

        $this->assertNotFalse($id);
        $this->assertSame('Ann Sanitized', (new Give_Donor($id))->name);
    }
}
