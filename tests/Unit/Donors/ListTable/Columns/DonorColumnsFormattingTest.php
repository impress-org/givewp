<?php

namespace Give\Tests\Unit\Donors\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\ListTable\Columns\DateCreatedColumn;
use Give\Donors\ListTable\Columns\DonationCountColumn;
use Give\Donors\ListTable\Columns\DonationRevenueColumn;
use Give\Donors\ListTable\Columns\LatestDonationColumn;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorsDataRepository;
use Give\Framework\Support\ValueObjects\Money;
use Give\Donors\ListTable\Columns\DonorInformationColumn;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\SwitchesNumberSeparators;

/**
 * @since TBD
 */
class DonorColumnsFormattingTest extends TestCase
{
    use RefreshDatabase;
    use SwitchesNumberSeparators;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        update_option('date_format', 'F j, Y');
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
    public function testRevenueFollowsTheBrowserLocaleWhenAutoFormatIsOn(): void
    {
        give_update_option('auto_format_currency', 'on');
        $donor = new Donor(['totalAmountDonated' => new Money(1234567890, 'USD')]);
        $column = new DonationRevenueColumn();

        $cell = html_entity_decode($column->getCellValue($donor, 'en-US-u-nu-thai'));

        $this->assertStringContainsString('๑๒,๓๔๕,๖๗๘.๙๐', $cell);
        $this->assertSame(['amount' => '12345678.90', 'currency' => 'USD'], $column->getRawValue($donor));
    }

    /**
     * @since TBD
     */
    public function testRevenueFollowsSiteSettingsWhenAutoFormatIsOff(): void
    {
        give_update_option('auto_format_currency', '');
        $donor = new Donor(['totalAmountDonated' => new Money(133128, 'USD')]);

        $this->assertStringContainsString('$1,331.28', (new DonationRevenueColumn())->getCellValue($donor, 'pt-BR'));
    }

    /**
     * @since TBD
     */
    public function testDonationCountIsACount(): void
    {
        $donor = new Donor(['totalNumberOfDonations' => 7]);
        $column = new DonationCountColumn();

        $this->assertSame('count', $column->getKind());
        $this->assertSame(7, $column->getRawValue($donor));
    }

    /**
     * @since TBD
     */
    public function testDonationCountFollowsTheLocale(): void
    {
        $donor = new Donor(['totalNumberOfDonations' => 1200]);
        $column = new DonationCountColumn();

        $this->assertSame('<div class="donationCount"><span>1,200</span></div>', $column->getCellValue($donor));
        $this->assertSame(
            '<div class="donationCount"><span>1.200</span></div>',
            $this->withSeparators('.', ',', function () use ($column, $donor) {
                return $column->getCellValue($donor);
            })
        );
    }

    /**
     * @since TBD
     */
    public function testDateCreatedNoLongerUsesAFixedPattern(): void
    {
        update_option('date_format', 'd/m/Y');

        $donor = new Donor([
            'createdAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC')),
        ]);
        $column = new DateCreatedColumn();

        $this->assertSame('29/11/2025 at 4:16 pm', $column->getCellValue($donor));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($donor));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testDonorWithoutDonationsShowsTheDash(): void
    {
        $donor = Donor::factory()->create();
        $column = new LatestDonationColumn();

        $cell = $column->getCellValue($donor);

        $this->assertNotSame('-', $cell);
        $this->assertStringContainsString('givewp-list-table-dash', $cell);
        $this->assertNull($column->getRawValue($donor));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testLatestDonationUsesTheSharedDateShape(): void
    {
        $donor = Donor::factory()->create();
        Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC')),
        ]);
        $column = new LatestDonationColumn();

        $this->assertSame('November 29, 2025 at 4:16 pm', $column->getCellValue($donor));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($donor));
    }

    /**
     * @since TBD
     */
    public function testLatestDonationRawValueAddsNoQuery(): void
    {
        global $wpdb;

        $donor = Donor::factory()->create();
        Donation::factory()->create(['donorId' => $donor->id, 'status' => DonationStatus::COMPLETE()]);
        $column = new LatestDonationColumn();
        $column->getCellValue($donor);

        $before = $wpdb->num_queries;
        $column->getRawValue($donor);

        $this->assertSame(0, $wpdb->num_queries - $before);
    }

    /**
     * @since TBD
     */
    public function testLatestDonationReadsThePageDataWithoutQuerying(): void
    {
        global $wpdb;

        $donorWithDonations = Donor::factory()->create();
        $donorWithoutDonations = Donor::factory()->create();
        $this->createDonation($donorWithDonations, '2025-01-10 10:00:00', DonationStatus::COMPLETE());
        $this->createDonation($donorWithDonations, '2025-11-29 16:16:00', DonationStatus::COMPLETE());
        // A pending donation is not a real donation yet, so it never counts as the latest one.
        $this->createDonation($donorWithDonations, '2026-01-01 08:00:00', DonationStatus::PENDING());

        $before = $wpdb->num_queries;
        $data = DonorsDataRepository::donors([$donorWithDonations->id, $donorWithoutDonations->id]);
        $this->assertSame(1, $wpdb->num_queries - $before);

        $column = new LatestDonationColumn();
        $column->setListTableData($data);

        $before = $wpdb->num_queries;
        $cellWithDonations = $column->getCellValue($donorWithDonations);
        $rawWithDonations = $column->getRawValue($donorWithDonations);
        $cellWithoutDonations = $column->getCellValue($donorWithoutDonations);
        $rawWithoutDonations = $column->getRawValue($donorWithoutDonations);

        $this->assertSame(0, $wpdb->num_queries - $before);
        $this->assertSame('November 29, 2025 at 4:16 pm', $cellWithDonations);
        $this->assertSame('2025-11-29T16:16:00Z', $rawWithDonations);
        $this->assertStringContainsString('givewp-list-table-dash', $cellWithoutDonations);
        $this->assertNull($rawWithoutDonations);
    }

    /**
     * @since TBD
     */
    public function testPageDataGivesTheSameValuesAsTheSingleDonorQuery(): void
    {
        $donor = Donor::factory()->create();
        $this->createDonation($donor, '2025-03-01 09:30:00', DonationStatus::COMPLETE());
        $this->createDonation($donor, '2025-06-15 18:45:00', DonationStatus::RENEWAL());

        $withPageData = new LatestDonationColumn();
        $withPageData->setListTableData(DonorsDataRepository::donors([$donor->id]));
        $withoutPageData = new LatestDonationColumn();

        $this->assertSame($withoutPageData->getCellValue($donor), $withPageData->getCellValue($donor));
        $this->assertSame($withoutPageData->getRawValue($donor), $withPageData->getRawValue($donor));
        $this->assertSame('2025-06-15T18:45:00Z', $withPageData->getRawValue($donor));
    }

    /**
     * @since TBD
     */
    private function createDonation(Donor $donor, string $date, DonationStatus $status): Donation
    {
        return Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => $status,
            'createdAt' => new DateTime($date, new DateTimeZone('UTC')),
        ]);
    }

    /**
     * @since TBD
     */
    public function testDonorCellEscapesTheNameAndEmail(): void
    {
        $text = '<script>alert(1)</script> & Co';
        $donor = new Donor(['id' => 1, 'firstName' => $text, 'lastName' => '', 'email' => $text]);

        $cell = (new DonorInformationColumn())->getCellValue($donor);

        $this->assertStringNotContainsString('<script>', $cell);
        $this->assertSame(2, substr_count($cell, esc_html($text)));
    }
}
