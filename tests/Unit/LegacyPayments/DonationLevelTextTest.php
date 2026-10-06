<?php

namespace Give\Tests\Unit\LegacyPayments;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * A donation is described by its own level: never by the first level when it has no level ID, and
 * by the amount it intended when it also paid a recovered fee.
 *
 * @since TBD
 *
 * @covers ::give_get_price_option_name
 * @covers ::give_get_donation_form_title
 */
class DonationLevelTextTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     *
     * @var array<int, array{value: int, label: string, checked: bool}>
     */
    private $levels = [
        ['value' => 10, 'label' => 'General Donation', 'checked' => true],
        ['value' => 25, 'label' => 'Building Donation', 'checked' => false],
        ['value' => 50, 'label' => 'Scholarship Donation', 'checked' => false],
    ];

    /**
     * @since TBD
     */
    public function testPriceOptionNameResolvesNumericLevelId(): void
    {
        $form = $this->createV3FormWithDescribedLevels();
        $levelId = array_key_last($this->levels);

        $this->assertSame(
            $this->levels[$levelId]['label'],
            give_get_price_option_name($form->id, (string)$levelId, 0, false)
        );
    }

    /**
     * @since TBD
     */
    public function testPriceOptionNameIsEmptyWhenLevelIdIsEmpty(): void
    {
        $form = $this->createV3FormWithDescribedLevels();

        $this->assertSame('', give_get_price_option_name($form->id, '', 0, false));
    }

    /**
     * @since TBD
     */
    public function testPriceOptionNameIsEmptyForCustomAmount(): void
    {
        $form = $this->createV3FormWithDescribedLevels();

        $this->assertSame('', give_get_price_option_name($form->id, 'custom', 0, false));
    }

    /**
     * @since TBD
     */
    public function testFormTitleMatchesLevelByAmountWithoutRecoveredFee(): void
    {
        $form = $this->createV3FormWithDescribedLevels();
        $level = $this->levels[array_key_last($this->levels)];

        /* Any fee works, as long as the total it produces is not itself a level amount. */
        $fee = Money::fromDecimal($level['value'] * 0.03, 'USD');

        $donation = Donation::factory()->create([
            'formId' => $form->id,
            'formTitle' => $form->title,
            'amount' => Money::fromDecimal($level['value'], 'USD')->add($fee),
            'feeAmountRecovered' => $fee,
            'levelId' => '',
        ]);

        $title = give_get_donation_form_title($donation->id, ['separator' => '-']);

        $this->assertStringContainsString($level['label'], $title);
    }

    /**
     * @since TBD
     */
    public function testFormTitleOmitsLevelForUnmatchedAmountWithoutLevelId(): void
    {
        $form = $this->createV3FormWithDescribedLevels();
        $customAmount = max(array_column($this->levels, 'value')) + 1;

        $donation = Donation::factory()->create([
            'formId' => $form->id,
            'formTitle' => $form->title,
            'amount' => Money::fromDecimal($customAmount, 'USD'),
            'feeAmountRecovered' => null,
            'levelId' => '',
        ]);

        $title = give_get_donation_form_title($donation->id, ['separator' => '-']);

        foreach ($this->levels as $level) {
            $this->assertStringNotContainsString($level['label'], $title);
        }
    }

    /**
     * @since TBD
     */
    public function testFormTitleOmitsLevelForCustomAmountEqualToALevel(): void
    {
        $form = $this->createV3FormWithDescribedLevels();
        $level = $this->levels[array_key_last($this->levels)];
        $fee = Money::fromDecimal($level['value'] * 0.03, 'USD');

        $donation = Donation::factory()->create([
            'formId' => $form->id,
            'formTitle' => $form->title,
            'amount' => Money::fromDecimal($level['value'], 'USD')->add($fee),
            'feeAmountRecovered' => $fee,
            'levelId' => 'custom',
        ]);

        $title = give_get_donation_form_title($donation->id, ['separator' => '-']);

        foreach ($this->levels as $describedLevel) {
            $this->assertStringNotContainsString($describedLevel['label'], $title);
        }
    }

    /**
     * @since TBD
     */
    private function createV3FormWithDescribedLevels(): DonationForm
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $form->blocks->findByName('givewp/donation-amount')
            ->setAttribute('priceOption', 'multi')
            ->setAttribute('customAmount', true)
            ->setAttribute('descriptionsEnabled', true)
            ->setAttribute('levels', $this->levels);

        $form->save();

        return $form;
    }
}
