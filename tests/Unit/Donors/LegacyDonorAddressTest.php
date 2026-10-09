<?php

namespace Give\Tests\Unit\Donors;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor;

/**
 * Covers the donor address queries of Give_Donor.
 *
 * @since TBD
 */
class LegacyDonorAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    private function legacyDonor(): Give_Donor
    {
        $donor = Donor::factory()->create(['addresses' => []]);

        return new Give_Donor($donor->id);
    }

    /**
     * @since TBD
     */
    private function address(string $line1, string $zip = '12345'): array
    {
        return [
            'line1' => $line1,
            'line2' => '',
            'city' => 'Springfield',
            'state' => 'IL',
            'country' => 'US',
            'zip' => $zip,
        ];
    }

    /**
     * Load the donor again, with an empty meta cache, so the address comes from the database.
     *
     * @since TBD
     */
    private function reload(Give_Donor $donor): Give_Donor
    {
        wp_cache_delete($donor->id, 'donor_meta');

        return new Give_Donor($donor->id);
    }

    /**
     * @since TBD
     */
    public function testAddAndReadAddressFromDatabase()
    {
        $donor = $this->legacyDonor();

        $this->assertTrue($donor->add_address('billing', $this->address('1 Main St')));

        $reloaded = $this->reload($donor);

        $this->assertSame('1 Main St', $reloaded->address['billing']['line1']);
        $this->assertSame('Springfield', $reloaded->address['billing']['city']);
        $this->assertSame('12345', $reloaded->address['billing']['zip']);
        $this->assertSame('1 Main St', $reloaded->get_donor_address(['address_type' => 'billing'])['line1']);
    }

    /**
     * @since TBD
     */
    public function testAddressOfOneDonorIsNotReadForAnotherDonor()
    {
        $first = $this->legacyDonor();
        $second = $this->legacyDonor();

        $first->add_address('billing', $this->address('1 Main St'));

        $this->assertEmpty($this->reload($second)->address);
    }

    /**
     * @since TBD
     */
    public function testAddMultipleAddressesGetsIncreasingIds()
    {
        $donor = $this->legacyDonor();

        $this->assertTrue($donor->add_address('billing[]', $this->address('1 Main St')));
        $this->assertTrue($donor->add_address('billing[]', $this->address('2 Side St', '54321')));

        $reloaded = $this->reload($donor);

        $this->assertSame('1 Main St', $reloaded->address['billing'][0]['line1']);
        $this->assertSame('2 Side St', $reloaded->address['billing'][1]['line1']);
        $this->assertSame('54321', $reloaded->address['billing'][1]['zip']);
    }

    /**
     * @since TBD
     */
    public function testAddAddressesOfDifferentTypes()
    {
        $donor = $this->legacyDonor();

        $donor->add_address('billing', $this->address('1 Main St'));
        $donor->add_address('shipping', $this->address('9 Ship Rd'));

        $reloaded = $this->reload($donor);

        $this->assertSame('1 Main St', $reloaded->address['billing']['line1']);
        $this->assertSame('9 Ship Rd', $reloaded->address['shipping']['line1']);
    }

    /**
     * @since TBD
     */
    public function testRemoveAddress()
    {
        $donor = $this->legacyDonor();

        $donor->add_address('billing', $this->address('1 Main St'));
        $donor->add_address('shipping', $this->address('9 Ship Rd'));

        $this->assertTrue($donor->remove_address('billing'));

        $reloaded = $this->reload($donor);

        $this->assertArrayNotHasKey('billing', $reloaded->address);
        $this->assertSame('9 Ship Rd', $reloaded->address['shipping']['line1']);
        $this->assertFalse($donor->remove_address('billing'));
    }

    /**
     * @since TBD
     */
    public function testRemoveOneOfMultipleAddresses()
    {
        $donor = $this->legacyDonor();

        $donor->add_address('billing[]', $this->address('1 Main St'));
        $donor->add_address('billing[]', $this->address('2 Side St', '54321'));

        $this->assertTrue($donor->remove_address('billing_0'));

        $reloaded = $this->reload($donor);

        $this->assertArrayNotHasKey(0, $reloaded->address['billing']);
        $this->assertSame('2 Side St', $reloaded->address['billing'][1]['line1']);
    }

    /**
     * @since TBD
     */
    public function testUpdateAddress()
    {
        $donor = $this->legacyDonor();

        $donor->add_address('billing[]', $this->address('1 Main St'));

        $this->assertTrue($donor->update_address('billing_0', $this->address('7 New St', '99999')));
        $this->assertFalse($donor->update_address('shipping_0', $this->address('7 New St', '99999')));

        $reloaded = $this->reload($donor);

        $this->assertSame('7 New St', $reloaded->address['billing'][0]['line1']);
        $this->assertSame('99999', $reloaded->address['billing'][0]['zip']);
    }
}
