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
    public function testCreateStoresTheSanitizedNotes()
    {
        $id = (new Give_Donor())->create([
            'name' => 'Ann Notes',
            'email' => 'ann-notes@example.com',
            'notes' => '<script>alert(1)</script><b>Hello</b>',
        ]);

        $this->assertNotFalse($id);
        // The notes column is not loaded into Give_Donor, so read the stored row.
        $this->assertSame('Hello', Give()->donors->get_donor_by('id', $id)->notes);
    }
}
