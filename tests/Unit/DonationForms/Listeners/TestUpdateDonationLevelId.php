<?php

namespace Give\Tests\Unit\DonationForms\Listeners;

use Give\DonationForms\Listeners\UpdateDonationLevelId;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\DonationForm as FormSchema;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;

/**
 * @covers \Give\DonationForms\Listeners\UpdateDonationLevelId
 *
 * @since 4.16.5
 */
class TestUpdateDonationLevelId extends TestCase
{
    /**
     * @since 4.16.5
     */
    public function testUpdatesLevelIdWhenEmpty()
    {
        // Mock DonationForm
        $donationForm = $this->getMockBuilder(DonationForm::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['schema'])
            ->getMock();

        // Mock Schema
        $schema = $this->getMockBuilder(FormSchema::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNodeByName'])
            ->getMock();

        // Mock Amount field
        $amountField = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLevels'])
            ->getMock();

        $amountField->method('getLevels')->willReturn([
            ['value' => 10.0, 'label' => 'Level 1'],
            ['value' => 20.0, 'label' => 'Level 2'],
            ['value' => 10.0, 'label' => 'Level 3']
        ]);

        $schema->method('getNodeByName')->with('amount')->willReturn($amountField);
        $donationForm->method('schema')->willReturn($schema);

        // Mock Donation with no levelId set
        $donation = $this->getMockBuilder(Donation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['intendedAmount', 'save'])
            ->getMock();

        $donation->levelId = '';
        $donation->method('intendedAmount')->willReturn(Money::fromDecimal(10.0, 'USD'));
        $donation->expects($this->once())->method('save');

        $listener = new UpdateDonationLevelId();
        $listener($donationForm, $donation);

        // It should match the first level index for amount 10.0, which is "0"
        $this->assertSame('0', $donation->levelId);
    }

    /**
     * @since 4.16.5
     */
    public function testDoesNotOverwriteCorrectLevelIdWhenDuplicateAmountExists()
    {
        // Mock DonationForm
        $donationForm = $this->getMockBuilder(DonationForm::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['schema'])
            ->getMock();

        // Mock Schema
        $schema = $this->getMockBuilder(FormSchema::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNodeByName'])
            ->getMock();

        // Mock Amount field
        $amountField = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLevels'])
            ->getMock();

        $amountField->method('getLevels')->willReturn([
            ['value' => 10.0, 'label' => 'Level 1'],
            ['value' => 20.0, 'label' => 'Level 2'],
            ['value' => 10.0, 'label' => 'Level 3']
        ]);

        $schema->method('getNodeByName')->with('amount')->willReturn($amountField);
        $donationForm->method('schema')->willReturn($schema);

        // Mock Donation with levelId already set to "2" (Level 3)
        $donation = $this->getMockBuilder(Donation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['intendedAmount', 'save'])
            ->getMock();

        $donation->levelId = '2';
        $donation->method('intendedAmount')->willReturn(Money::fromDecimal(10.0, 'USD'));
        
        // Save should NOT be called because it returns early (already correct matching level)
        $donation->expects($this->never())->method('save');

        $listener = new UpdateDonationLevelId();
        $listener($donationForm, $donation);

        // levelId should remain "2" and not be overwritten with "0"
        $this->assertSame('2', $donation->levelId);
    }
}
