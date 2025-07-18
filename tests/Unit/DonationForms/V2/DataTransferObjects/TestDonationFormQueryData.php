<?php

namespace Give\Tests\Unit\DonationForms\V2\DataTransferObjects;

use DateTime;
use Give\DonationForms\V2\DataTransferObjects\DonationFormQueryData;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Properties\DonationFormLevel;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys as LegacyDonationFormMetaKeys;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use stdClass;

/**
 * @unreleased
 */
final class TestDonationFormQueryData extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testFromObjectShouldCreateDonationFormQueryDataFromQueryObject()
    {
        $v2Form = $this->createSimpleDonationForm();
        $queryObject = $this->createMockQueryObject($v2Form);

        $donationFormQueryData = DonationFormQueryData::fromObject($queryObject);

        $this->assertInstanceOf(DonationFormQueryData::class, $donationFormQueryData);
        $this->assertEquals($v2Form->id, $donationFormQueryData->id);
        $this->assertEquals($v2Form->title, $donationFormQueryData->title);
        $this->assertInstanceOf(Money::class, $donationFormQueryData->totalAmountDonated);
        $this->assertInstanceOf(DateTime::class, $donationFormQueryData->createdAt);
        $this->assertInstanceOf(DateTime::class, $donationFormQueryData->updatedAt);
        $this->assertInstanceOf(DonationFormStatus::class, $donationFormQueryData->status);
    }

    /**
     * @unreleased
     */
    public function testFromObjectShouldHandleNullGoalFormat()
    {
        $v2Form = $this->createSimpleDonationForm();
        $queryObject = $this->createMockQueryObject($v2Form);
        $queryObject->goalFormat = null;

        $donationFormQueryData = DonationFormQueryData::fromObject($queryObject);

        $this->assertInstanceOf(DonationFormQueryData::class, $donationFormQueryData);
        $this->assertEquals(GoalType::AMOUNT(), $donationFormQueryData->goalSettings->goalType);
    }

    /**
     * @unreleased
     */
    public function testFromObjectShouldHandleEmptyGoalFormat()
    {
        $v2Form = $this->createSimpleDonationForm();
        $queryObject = $this->createMockQueryObject($v2Form);
        $queryObject->goalFormat = '';

        $donationFormQueryData = DonationFormQueryData::fromObject($queryObject);

        $this->assertInstanceOf(DonationFormQueryData::class, $donationFormQueryData);
        $this->assertEquals(GoalType::AMOUNT(), $donationFormQueryData->goalSettings->goalType);
    }

    /**
     * @unreleased
     */
    public function testToDonationFormShouldReturnDonationForm()
    {
        $v2Form = $this->createSimpleDonationForm();
        $queryObject = $this->createMockQueryObject($v2Form);

        $donationFormQueryData = DonationFormQueryData::fromObject($queryObject);
        $donationForm = $donationFormQueryData->toDonationForm();

        $this->assertInstanceOf(DonationForm::class, $donationForm);
        $this->assertEquals($v2Form->id, $donationForm->id);
        $this->assertEquals($v2Form->title, $donationForm->title);
    }

    /**
     * @dataProvider donationFormLevelsProvider
     * @unreleased
     */
    public function testGetDonationFormLevels(string $priceOption, array $expectedLevels)
    {
        $dto = new DonationFormQueryData();
        $queryObject = new stdClass();
        $queryObject->{LegacyDonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()} = $priceOption;

        if ($priceOption === 'multi') {
            $queryObject->{LegacyDonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()} = serialize($expectedLevels);
        } elseif ($priceOption === 'set') {
            $queryObject->{LegacyDonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase()} = $expectedLevels[0] ?? null;
        }

        $levels = $dto->getDonationFormLevels($queryObject);

        if (empty($expectedLevels)) {
            $this->assertEmpty($levels);
        } else {
            $this->assertNotEmpty($levels);
            $this->assertInstanceOf(DonationFormLevel::class, $levels[0]);
        }
    }

    /**
     * @unreleased
     */
    public function testGetDonationFormLevelsShouldReturnEmptyArrayWhenMultiLevelsIsNull()
    {
        $dto = new DonationFormQueryData();
        $queryObject = new stdClass();
        $queryObject->{LegacyDonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()} = 'multi';
        $queryObject->{LegacyDonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()} = null;

        $levels = $dto->getDonationFormLevels($queryObject);

        $this->assertSame([], $levels);
    }

    /**
     * @unreleased
     */
    public function testGetDonationFormLevelsShouldReturnEmptyArrayWhenSetPriceIsNull()
    {
        $dto = new DonationFormQueryData();
        $queryObject = new stdClass();
        $queryObject->{LegacyDonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()} = 'set';
        $queryObject->{LegacyDonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase()} = null;

        $levels = $dto->getDonationFormLevels($queryObject);

        $this->assertSame([], $levels);
    }

    /**
     * @dataProvider goalTypeProvider
     * @unreleased
     */
    public function testConvertGoalTypeShouldReturnCorrectGoalType(string $type, bool $isRecurring, GoalType $expected)
    {
        $dto = new DonationFormQueryData();
        $result = $dto->convertGoalType($type, $isRecurring);

        $this->assertEquals($expected, $result);
    }

    /**
     * @unreleased
     */
    public function testConvertGoalTypeShouldHandleNullType()
    {
        $dto = new DonationFormQueryData();
        $result = $dto->convertGoalType('', false);

        $this->assertEquals(GoalType::AMOUNT(), $result);
    }

    /**
     * @unreleased
     */
    public function testConvertGoalTypeShouldHandleInvalidType()
    {
        $dto = new DonationFormQueryData();
        $result = $dto->convertGoalType('invalid_type', false);

        $this->assertEquals(GoalType::AMOUNT(), $result);
    }

    /**
     * @unreleased
     */
    public function testConvertGoalTypeShouldReturnRecurringGoalTypeWhenIsRecurringIsTrue()
    {
        $dto = new DonationFormQueryData();
        $result = $dto->convertGoalType('invalid_type', true);

        $this->assertEquals(GoalType::AMOUNT_FROM_SUBSCRIPTIONS(), $result);
    }

    /**
     * @unreleased
     */
    public function testFromObjectShouldHandleNullGoalFormatInGoalSettings()
    {
        $v2Form = $this->createSimpleDonationForm();
        $queryObject = $this->createMockQueryObject($v2Form);
        $queryObject->goalFormat = null;

        $donationFormQueryData = DonationFormQueryData::fromObject($queryObject);

        $this->assertEquals(GoalType::AMOUNT(), $donationFormQueryData->goalSettings->goalType);
    }

    /**
     * @unreleased
     */
    public function donationFormLevelsProvider(): array
    {
        return [
            'multi with levels' => [
                'multi',
                [
                    [
                        '_give_id' => ['level_id' => 1],
                        '_give_amount' => '100',
                        '_give_text' => 'Level 1',
                    ],
                    [
                        '_give_id' => ['level_id' => 2],
                        '_give_amount' => '200',
                        '_give_text' => 'Level 2',
                    ],
                ]
            ],
            'multi without levels' => [
                'multi',
                []
            ],
            'set with amount' => [
                'set',
                ['100']
            ],
            'set without amount' => [
                'set',
                []
            ],
            'default case' => [
                'other',
                []
            ],
        ];
    }

    /**
     * @unreleased
     */
    public function goalTypeProvider(): array
    {
        return [
            'donation type' => ['donation', false, GoalType::DONATIONS()],
            'donations type' => ['donations', false, GoalType::DONATIONS()],
            'donation recurring' => ['donation', true, GoalType::SUBSCRIPTIONS()],
            'donations recurring' => ['donations', true, GoalType::SUBSCRIPTIONS()],
            'donors type' => ['donors', false, GoalType::DONORS()],
            'donors recurring' => ['donors', true, GoalType::DONORS_FROM_SUBSCRIPTIONS()],
            'subscriptions type' => ['subscriptions', false, GoalType::SUBSCRIPTIONS()],
            'subscriptions recurring' => ['subscriptions', true, GoalType::SUBSCRIPTIONS()],
            'donorsFromSubscriptions type' => ['donorsFromSubscriptions', false, GoalType::DONORS_FROM_SUBSCRIPTIONS()],
            'donorsFromSubscriptions recurring' => ['donorsFromSubscriptions', true, GoalType::DONORS_FROM_SUBSCRIPTIONS()],
            'amountFromSubscriptions type' => ['amountFromSubscriptions', false, GoalType::AMOUNT_FROM_SUBSCRIPTIONS()],
            'amountFromSubscriptions recurring' => ['amountFromSubscriptions', true, GoalType::AMOUNT_FROM_SUBSCRIPTIONS()],
            'default type' => ['amount', false, GoalType::AMOUNT()],
            'default recurring' => ['amount', true, GoalType::AMOUNT_FROM_SUBSCRIPTIONS()],
        ];
    }

    /**
     * Create a mock query object from a donation form
     *
     * @unreleased
     */
    private function createMockQueryObject(DonationForm $form): stdClass
    {
        $queryObject = new stdClass();
        $queryObject->id = $form->id;
        $queryObject->title = $form->title;
        $queryObject->createdAt = $form->createdAt->format('Y-m-d H:i:s');
        $queryObject->updatedAt = $form->updatedAt->format('Y-m-d H:i:s');
        $queryObject->status = $form->status->getValue();
        $queryObject->{LegacyDonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase()} = $form->goalOption ? 'enabled' : 'disabled';
        $queryObject->{LegacyDonationFormMetaKeys::FORM_EARNINGS()->getKeyAsCamelCase()} = $form->totalAmountDonated->getAmount();
        $queryObject->{LegacyDonationFormMetaKeys::FORM_SALES()->getKeyAsCamelCase()} = $form->totalNumberOfDonations;
        $queryObject->{LegacyDonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()} = 'set';
        $queryObject->{LegacyDonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase()} = 100;
        $queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()} = null;
        $queryObject->goalOption = $form->goalOption ? 'enabled' : 'disabled';
        $queryObject->goalFormat = 'amount';
        $queryObject->recurringGoalFormat = false;
        $queryObject->goalAmount = $form->goalSettings->goalAmount;
        $queryObject->settings = false;

        return $queryObject;
    }
} 