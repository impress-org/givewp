<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\ValidateReceiptViewPermission;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;

/**
 * @since 4.0.0
 */
class ValidateReceiptViewPermissionTest extends TestCase
{
    /**
     * @since 4.0.0
     */
    private ValidateReceiptViewPermission $action;

    /**
     * @since 4.0.0
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateReceiptViewPermission();
    }

    /**
     * @since 4.0.0
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($_GET['receipt_id']);
    }

    /**
     * @since 4.0.0
     */
    public function testReturnsOriginalPermissionWhenReceiptIdIsNotSet(): void
    {
        $result = ($this->action)(false, 123);
        $this->assertFalse($result);

        $result = ($this->action)(true, 123);
        $this->assertTrue($result);
    }

    /**
     * @since 4.0.0
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
     * @since 4.0.0
     */
    public function testReturnsOriginalPermissionWhenDonationNotFound(): void
    {
        $_GET['receipt_id'] = 'ABC123';

        $result = ($this->action)(false, 123);
        $this->assertFalse($result);
    }

    /**
     * @since 4.0.0
     *
     * @throws \Exception
     */
    public function testReturnsOriginalPermissionWhenDonationIdDoesNotMatch(): void
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $_GET['receipt_id'] = $donation->purchaseKey;

        $result = ($this->action)(false, 123);
        $this->assertFalse($result);
    }

    /**
     * @since 4.0.0
     *
     * @throws \Exception
     */
    public function testReturnsTrueWhenDonationIdMatches(): void
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $_GET['receipt_id'] = $donation->purchaseKey;

        $result = ($this->action)(false, $donation->id);
        $this->assertTrue($result);
    }
}
