<?php

namespace Give\Tests\Unit\DonationForms\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\ListTable\Columns\DateCreatedColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationCountColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationRevenueColumn;
use Give\DonationForms\V2\ListTable\Columns\GoalColumn;
use Give\DonationForms\V2\ListTable\Columns\LevelsColumn;
use Give\DonationForms\V2\ListTable\Columns\StatusColumn;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Properties\DonationFormLevel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\SwitchesNumberSeparators;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @since TBD
 */
class FormColumnsFormattingTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;
    use SwitchesNumberSeparators;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        update_option('date_format', 'd/m/Y');
        update_option('time_format', 'g:i a');
        update_option('timezone_string', 'UTC');
        foreach (
            [
                'currency' => 'USD',
                'currency_position' => 'before',
                'thousands_separator' => ',',
                'decimal_separator' => '.',
                'number_decimals' => 2,
                'auto_format_currency' => 'on',
            ] as $key => $value
        ) {
            give_update_option($key, $value);
        }
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['date_format', 'time_format', 'timezone_string'] as $option) {
            delete_option($option);
        }

        foreach (['currency', 'currency_position', 'thousands_separator', 'decimal_separator', 'number_decimals', 'auto_format_currency'] as $key) {
            give_delete_option($key);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testRevenueUsesTheSharedMoneyFormatWhateverLocaleIsPassed(): void
    {
        $form = new DonationForm(['id' => 9]);
        $column = $this->revenueColumn(1331.28);

        $this->assertStringContainsString('>$1,331.28</a>', $column->getCellValue($form, 'pt_BR'));
        $this->assertSame(['amount' => '1331.28', 'currency' => 'USD'], $column->getRawValue($form));
        $this->assertSame('money', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testLevelsRangeIgnoresTheLocale(): void
    {
        $form = new DonationForm(['levels' => [$this->level(50), $this->level(10), $this->level(25)]]);
        $column = new LevelsColumn();

        $this->assertStringContainsString('$10.00 - $50.00', $column->getCellValue($form, 'pt_BR'));
        $this->assertSame('text', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testSingleLevelIgnoresTheLocale(): void
    {
        $form = new DonationForm(['levels' => [$this->level(25)]]);

        $this->assertStringContainsString('<span>$25.00</span>', (new LevelsColumn())->getCellValue($form, 'pt_BR'));
    }

    /**
     * @since TBD
     */
    public function testDateCreatedFollowsTheSiteSettings(): void
    {
        $form = new DonationForm(['createdAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC'))]);
        $column = new DateCreatedColumn();

        $this->assertSame('29/11/2025 at 4:16 pm', $column->getCellValue($form));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($form));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testCountAndStatusKinds(): void
    {
        $this->assertSame('count', (new DonationCountColumn())->getKind());
        $this->assertSame('status', (new StatusColumn())->getKind());
    }

    /**
     * @since TBD
     */
    public function testGoalDataFormatsAmountGoalsWithTheSharedMoneyFormat(): void
    {
        $form = $this->createSimpleDonationForm();
        $goal = DonationFormDataRepository::forms([$form])->getGoalData($form);

        $this->assertSame('$0.00', $goal['actualFormatted']);
        $this->assertSame('$' . number_format((float) $goal['goal'], 2), $goal['goalFormatted']);
    }

    /**
     * @since TBD
     */
    public function testFormWithoutAGoalShowsTheLabeledDash(): void
    {
        $form = $this->createSimpleDonationForm();
        $form->goalSettings->enableDonationGoal = false;

        $cell = (new GoalColumn())->getCellValue($form);

        $this->assertStringContainsString('givewp-list-table-dash', $cell);
        $this->assertStringContainsString('No Goal Set', $cell);
    }

    /**
     * @since TBD
     */
    public function testCountGoalCellFollowsTheLocale(): void
    {
        $form = $this->createSimpleDonationForm();
        $form->goalSettings->enableDonationGoal = true;
        $column = $this->goalColumnWith($this->goalData(87, 1000000, false), $form);

        $english = $column->getCellValue($form);
        $german = $this->withSeparators('.', ',', function () use ($column, $form) {
            return $column->getCellValue($form);
        });

        $this->assertStringContainsString('1,000,000', $english);
        $this->assertStringContainsString('1.000.000', $german);
        $this->assertStringNotContainsString('1000000', $english . $german);
    }

    /**
     * @since TBD
     */
    public function testMoneyGoalCellKeepsTheMoneyText(): void
    {
        $form = $this->createSimpleDonationForm();
        $form->goalSettings->enableDonationGoal = true;
        $data = $this->goalData(87, 1000000, true);
        $data['actualFormatted'] = '$87.00';
        $data['goalFormatted'] = '$1,000,000.00';

        $german = $this->withSeparators('.', ',', function () use ($data, $form) {
            return $this->goalColumnWith($data, $form)->getCellValue($form);
        });

        $this->assertStringContainsString('$1,000,000.00', $german);
    }

    /**
     * @since TBD
     */
    public function testDonationCountFollowsTheLocaleAndKeepsTheRawNumber(): void
    {
        $form = $this->createSimpleDonationForm();
        $stub = $this->getMockBuilder(DonationFormDataRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDonationsCount'])
            ->getMock();
        $stub->method('getDonationsCount')->willReturn(1200);
        $column = new DonationCountColumn();
        $column->setListTableData($stub);

        $this->assertSame('<div class="donationCount"><span>1,200</span></div>', $column->getCellValue($form));
        $this->assertSame(
            '<div class="donationCount"><span>1.200</span></div>',
            $this->withSeparators('.', ',', function () use ($column, $form) {
                return $column->getCellValue($form);
            })
        );
        $this->assertSame(1200, $column->getRawValue($form));
    }

    /**
     * @since TBD
     */
    private function goalData(int $actual, int $goal, bool $isMoney): array
    {
        return [
            'actual' => $actual,
            'goal' => $goal,
            'actualFormatted' => $actual,
            'goalFormatted' => $goal,
            'percentage' => round($actual / $goal * 100, 2),
            'typeIsMoney' => $isMoney,
        ];
    }

    /**
     * @since TBD
     */
    private function goalColumnWith(array $goalData, DonationForm $form): GoalColumn
    {
        $stub = $this->getMockBuilder(DonationFormDataRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getGoalData'])
            ->getMock();
        $stub->method('getGoalData')->willReturn($goalData);
        $column = new GoalColumn();
        $column->setListTableData($stub);

        return $column;
    }

    /**
     * @since TBD
     */
    private function level(int $dollars): DonationFormLevel
    {
        return DonationFormLevel::fromPrice((string) $dollars);
    }

    /**
     * @since TBD
     */
    private function revenueColumn(float $revenue): DonationRevenueColumn
    {
        $stub = $this->getMockBuilder(DonationFormDataRepository::class)
            ->onlyMethods(['getRevenue'])
            ->getMock();
        $stub->method('getRevenue')->willReturn($revenue);

        $column = new DonationRevenueColumn();
        $column->setListTableData($stub);

        return $column;
    }
}
