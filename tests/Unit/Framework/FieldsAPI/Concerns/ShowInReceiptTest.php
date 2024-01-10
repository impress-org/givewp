<?php

namespace Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\ShowInReceipt;
use Give\Tests\TestCase;


/**
 * @covers ShowInReceipt
 */
class ShowInReceiptTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testSettingTheReceiptLabel(): void
    {
        /** @var ShowInReceipt $mock */
        $mock = $this->getMockForTrait(ShowInReceipt::class);

        $mock->receiptLabel('test');
        $this->assertTrue($mock->hasReceiptLabel());
        $this->assertSame('test', $mock->getReceiptLabel());
    }

    /**
     * @unreleased
     */
    public function testSettingTheReceiptValue(): void
    {
        /** @var ShowInReceipt $mock */
        $mock = $this->getMockForTrait(ShowInReceipt::class);

        $value = function ($field, $donation) {
            return 'test';
        };

        $mock->receiptValue($value);
        $this->assertTrue($mock->hasReceiptValue());
        self::assertIsCallable($mock->getReceiptValue());
        $this->assertSame($value, $mock->getReceiptValue());
    }

    /**
     * @unreleased
     */
    public function testShouldShowInReceipt(): void
    {
        /** @var ShowInReceipt $mock */
        $mock = $this->getMockForTrait(ShowInReceipt::class);

        $mock->showInReceipt();
        $this->assertTrue($mock->shouldShowInReceipt());
    }
}
