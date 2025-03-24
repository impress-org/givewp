<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\ValidateReceiptViewPermission;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class ValidateReceiptViewPermissionTest extends TestCase
{
    /**
     * @unreleased
     */
    private ValidateReceiptViewPermission $action;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateReceiptViewPermission();
    }

    /**
     * @unreleased
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($_GET['receipt_id']);
    }

    /**
     * @unreleased
     */
    public function testReturnsOriginalPermissionWhenReceiptIdIsNotSet(): void
    {
        $result = ($this->action)(false, 123);
        $this->assertFalse($result);

        $result = ($this->action)(true, 123);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testReturnsOriginalPermissionWhenReceiptIdIsEmpty(): void
    {
        $_GET['receipt_id'] = '';

        $result = ($this->action)(false, 123);
        $this->assertFalse($result);

        $result = ($this->action)(true, 123);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testReturnsOriginalPermissionWhenDonationNotFound(): void
    {
        $_GET['receipt_id'] = 'ABC123';

        $result = ($this->action)(false, 123);
        $this->assertFalse($result);
    }

    /**
     * @unreleased
     *
     * @throws \Exception
     */
    public function testReturnsOriginalPermissionWhenDonationIdDoesNotMatch(): void
    {
        $donation = Donation::factory()->create();
        $_GET['receipt_id'] = $donation->purchaseKey;

        $result = ($this->action)(false, 123);
        $this->assertFalse($result);
    }

    /**
     * @unreleased
     *
     * @throws \Exception
     */
    public function testReturnsTrueWhenDonationIdMatches(): void
    {
        $donation = Donation::factory()->create();
        $_GET['receipt_id'] = $donation->purchaseKey;

        $result = ($this->action)(false, $donation->id);
        $this->assertTrue($result);
    }
}
