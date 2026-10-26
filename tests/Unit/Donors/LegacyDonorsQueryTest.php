<?php

namespace Give\Tests\Unit\Donors;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache;
use Give_Donor_List_Table;
use Give_Donor_Reports_Table;
use Give_Donors_Query;

/**
 * Covers the queries of Give_Donors_Query.
 *
 * The tests for several forms, a form list with non-numeric ids, an unknown fields value, an
 * invalid compare value, and a search term with a quote or a wildcard were added after the SQL
 * fix, because they check the new behavior of the form filter, the allowlists and the escaping of
 * the search term. Every other test was written first and passed on the unchanged code.
 *
 * @since 4.19.1
 */
class LegacyDonorsQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.1
     */
    public function setUp(): void
    {
        parent::setUp();

        // The query results are cached by SQL text, and the tables are emptied between tests.
        Give_Cache::flush_cache(true);
    }

    /**
     * @since 4.19.1
     */
    private function makeDonor(string $name, string $email): Donor
    {
        return Donor::factory()->create([
            'name' => $name,
            'firstName' => $name,
            'lastName' => '',
            'email' => $email,
            'additionalEmails' => [],
            'addresses' => [],
        ]);
    }

    /**
     * @since 4.19.1
     */
    private function donate(Donor $donor, DonationForm $form): void
    {
        Donation::factory()->create([
            'formId' => $form->id,
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
        ]);
    }

    /**
     * @since 4.19.1
     *
     * @return string[]
     */
    private function names(array $args): array
    {
        $donors = (new Give_Donors_Query($args))->get_donors();
        $names = array_map(static function ($donor) {
            return $donor->name;
        }, (array)$donors);

        sort($names);

        return $names;
    }

    /**
     * @since 4.19.1
     */
    public function testSearchByNameMatchesPartOfTheName()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Bobby Tables'], $this->names(['s' => 'Bobby']));
    }

    /**
     * @since 4.19.1
     */
    public function testSearchWithNamePrefixMatchesPartOfTheName()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Alice Smith'], $this->names(['s' => 'name:Alic']));
    }

    /**
     * @since 4.19.1
     */
    public function testSearchByEmailMatchesPartOfTheEmail()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Alice Smith'], $this->names(['s' => 'alice@example.org']));
    }

    /**
     * @since 4.19.1
     */
    public function testSearchByNumericValueMatchesTheDonorId()
    {
        $bobby = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Bobby Tables'], $this->names(['s' => (string)$bobby->id]));
    }

    /**
     * @since 4.19.1
     */
    public function testEmailArgumentMatchesOneOrManyEmails()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');
        $this->makeDonor('Carol Jones', 'carol@example.org');

        $this->assertSame(['Bobby Tables'], $this->names(['email' => 'bobby@example.org']));
        $this->assertSame(
            ['Alice Smith', 'Carol Jones'],
            $this->names(['email' => ['alice@example.org', 'carol@example.org']])
        );
    }

    /**
     * @since 4.19.1
     */
    public function testSingleFormFilterReturnsTheDonorsOfThatForm()
    {
        $formOne = DonationForm::factory()->create();
        $formTwo = DonationForm::factory()->create();
        $alice = $this->makeDonor('Alice Smith', 'alice@example.org');
        $bobby = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Carol Jones', 'carol@example.org');
        $this->donate($alice, $formOne);
        $this->donate($bobby, $formTwo);

        $this->assertSame(['Alice Smith'], $this->names(['give_forms' => $formOne->id]));
        $this->assertSame(['Bobby Tables'], $this->names(['give_forms' => [$formTwo->id]]));
    }

    /**
     * @since 4.19.1
     */
    public function testEmptyFormListDoesNotFilterDonors()
    {
        $form = DonationForm::factory()->create();
        $alice = $this->makeDonor('Alice Smith', 'alice@example.org');
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->donate($alice, $form);

        // The donation factory also creates a donor of its own, so check that nobody was filtered out.
        $this->assertSame($this->names([]), $this->names(['give_forms' => []]));
        $this->assertContains('Bobby Tables', $this->names(['give_forms' => []]));
    }

    /**
     * @since 4.19.1
     */
    public function testFieldsArgumentReturnsOnlyTheRequestedColumns()
    {
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $one = (new Give_Donors_Query(['fields' => 'id']))->get_donors();
        $this->assertSame(['id'], array_keys(get_object_vars($one[0])));

        $many = (new Give_Donors_Query(['fields' => ['id', 'email']]))->get_donors();
        $this->assertSame(['id', 'email'], array_keys(get_object_vars($many[0])));
        $this->assertSame('alice@example.org', $many[0]->email);
    }

    /**
     * @since 4.19.1
     */
    public function testDonationCountCompareReturnsDonorsAboveTheAmount()
    {
        $this->makeDonor('Alice Smith', 'alice@example.org');
        $frequent = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $frequent->totalNumberOfDonations = 3;
        $frequent->save();

        $this->assertSame(
            ['Bobby Tables'],
            $this->names(['donation_count' => ['compare' => '>', 'amount' => 1]])
        );
        $this->assertSame(['Bobby Tables'], $this->names(['donation_count' => 1]));
    }

    /**
     * @since 4.19.1
     */
    public function testDonationCountNotEqualOperatorReturnsDonorsWithAnotherCount()
    {
        $once = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $once->totalNumberOfDonations = 1;
        $once->save();
        $often = $this->makeDonor('Alice Smith', 'alice@example.org');
        $often->totalNumberOfDonations = 3;
        $often->save();

        $this->assertSame(
            ['Alice Smith'],
            $this->names(['donation_count' => ['compare' => '<>', 'amount' => 1]])
        );
    }

    /**
     * @since 4.19.1
     */
    public function testSeveralFormsMatchTheDonorsOfAnyListedForm()
    {
        $formOne = DonationForm::factory()->create();
        $formTwo = DonationForm::factory()->create();
        $formThree = DonationForm::factory()->create();
        $alice = $this->makeDonor('Alice Smith', 'alice@example.org');
        $bobby = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $carol = $this->makeDonor('Carol Jones', 'carol@example.org');
        $this->donate($alice, $formOne);
        $this->donate($bobby, $formTwo);
        $this->donate($carol, $formThree);

        $this->assertSame(
            ['Alice Smith', 'Bobby Tables'],
            $this->names(['give_forms' => [$formOne->id, $formTwo->id]])
        );
        $this->assertSame(
            ['Alice Smith', 'Bobby Tables'],
            $this->names(['give_forms' => "{$formOne->id},{$formTwo->id}"])
        );
    }

    /**
     * @since 4.19.1
     */
    public function testFormListDropsNonNumericIds()
    {
        $form = DonationForm::factory()->create();
        $alice = $this->makeDonor('Alice Smith', 'alice@example.org');
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->donate($alice, $form);

        $this->assertSame(['Alice Smith'], $this->names(['give_forms' => [$form->id, 'abc']]));
        $this->assertSame([], $this->names(['give_forms' => ['abc']]));
    }

    /**
     * @since 4.19.1
     */
    public function testNegativeFormIdDoesNotMatchTheDonorsOfTheSameFormId()
    {
        $form = DonationForm::factory()->create();
        $alice = $this->makeDonor('Alice Smith', 'alice@example.org');
        $this->donate($alice, $form);

        $this->assertSame(['Alice Smith'], $this->names(['give_forms' => [$form->id]]));
        $this->assertSame([], $this->names(['give_forms' => [-$form->id]]));
    }

    /**
     * @since 4.19.1
     */
    public function testNegativeDonationCountAmountKeepsItsSign()
    {
        $this->makeDonor('Alice Smith', 'alice@example.org');
        $this->makeDonor('Bobby Tables', 'bobby@example.org');

        $this->assertSame(
            ['Alice Smith', 'Bobby Tables'],
            $this->names(['donation_count' => ['compare' => '>', 'amount' => -1]])
        );
    }

    /**
     * @since 4.19.1
     */
    public function testDonationAmountIsFormattedWithADecimalPoint()
    {
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $sql = (new Give_Donors_Query(['donation_amount' => ['compare' => '>', 'amount' => 10.5]]))->get_sql();
        $this->assertStringContainsString('purchase_value>10.5', $sql);

        $sql = (new Give_Donors_Query(['donation_amount' => ['compare' => '>', 'amount' => 10]]))->get_sql();
        $this->assertStringContainsString('purchase_value>10 ', $sql . ' ');
    }

    /**
     * @since 4.19.1
     */
    public function testUnknownFieldFallsBackToAllColumns()
    {
        global $wpdb;

        $this->makeDonor('Alice Smith', 'alice@example.org');

        $donors = (new Give_Donors_Query(['fields' => 'nope, (SELECT 1)']))->get_donors();

        $this->assertSame('', $wpdb->last_error);
        $this->assertCount(1, $donors);
        $this->assertSame('Alice Smith', $donors[0]->name);

        $donors = (new Give_Donors_Query(['fields' => ['id', 'nope']]))->get_donors();

        $this->assertSame('', $wpdb->last_error);
        $this->assertSame('Alice Smith', $donors[0]->name);
    }

    /**
     * @since 4.19.1
     */
    public function testInvalidCompareFallsBackToTheEqualOperator()
    {
        global $wpdb;

        $once = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $once->totalNumberOfDonations = 1;
        $once->save();
        $often = $this->makeDonor('Alice Smith', 'alice@example.org');
        $often->totalNumberOfDonations = 3;
        $often->save();

        // The operator falls back to "=" and the amount is cast, so "1 OR 1=1" becomes 1.
        $names = $this->names(['donation_count' => ['compare' => '; DROP TABLE x', 'amount' => '1 OR 1=1']]);

        $this->assertSame('', $wpdb->last_error);
        $this->assertSame(['Bobby Tables'], $names);

        // The amount "x" becomes 0, and every donor has a total of 0.
        $names = $this->names(['donation_amount' => ['compare' => '; DROP', 'amount' => 'x']]);

        $this->assertSame('', $wpdb->last_error);
        $this->assertSame(['Alice Smith', 'Bobby Tables'], $names);
    }

    /**
     * @since 4.19.1
     */
    public function testSearchTermWithAQuoteMatchesTheDonor()
    {
        global $wpdb;

        $this->makeDonor("Pat O'Brien", 'pat@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(["Pat O'Brien"], $this->names(['s' => "O'Brien"]));
        $this->assertSame(["Pat O'Brien"], $this->names(['s' => "name:O'Brien"]));
        $this->assertSame('', $wpdb->last_error);
    }

    /**
     * @since 4.19.1
     */
    public function testUnderscoreAndPercentInTheSearchTermAreLiteral()
    {
        $this->makeDonor('Ann_Lee', 'annlee@example.org');
        $this->makeDonor('AnnxLee', 'annxlee@example.org');
        $this->makeDonor('100% Fan', 'fan@example.org');
        $this->makeDonor('1000 Fan', 'fan2@example.org');

        $this->assertSame(['Ann_Lee'], $this->names(['s' => 'n_L']));
        $this->assertSame(['100% Fan'], $this->names(['s' => '100%']));
    }

    /**
     * @since 4.19.1
     */
    public function testHostileSearchTermMatchesNothingAndGivesNoSqlError()
    {
        global $wpdb;

        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame([], $this->names(['s' => "x' OR '1'='1"]));
        $this->assertSame('', $wpdb->last_error);
        $this->assertSame([], $this->names(['s' => "name:x' OR '1'='1"]));
        $this->assertSame('', $wpdb->last_error);
        $this->assertSame([], $this->names(['s' => "x' OR '1'='1@example.org"]));
        $this->assertSame('', $wpdb->last_error);
    }

    /**
     * @since 4.19.1
     */
    public function testGetSqlIsStableAndHasNoPlaceholderHash()
    {
        global $wpdb;

        $query = new Give_Donors_Query(['s' => 'name:100%', 'email' => 'a@example.org']);

        $first = $query->get_sql();
        $second = $query->get_sql();

        $this->assertSame($first, $second);
        $this->assertSame($first, $wpdb->remove_placeholder_escape($first));
        // MySQL reads the two backslashes as one, so the percent sign is a literal in the LIKE.
        $this->assertStringContainsString("name LIKE '%100" . '\\\\' . "%%'", $first);
    }

    /**
     * @since 4.19.1
     */
    public function testDonorListTableUnslashesTheSearchTerm()
    {
        $this->loadListTables();
        $_GET['s'] = wp_slash("O'Brien");

        try {
            $table = new Give_Donor_List_Table();

            $this->assertSame("O'Brien", $table->get_search());
            $this->assertSame("O'Brien", $table->get_donor_query()['s']);
        } finally {
            unset($_GET['s']);
        }
    }

    /**
     * @since 4.19.1
     */
    public function testDonorReportsTableUnslashesTheSearchTerm()
    {
        $this->loadListTables();
        $_GET['s'] = wp_slash("O'Brien");

        try {
            $this->assertSame("O'Brien", (new Give_Donor_Reports_Table())->get_search());
        } finally {
            unset($_GET['s']);
        }
    }

    /**
     * @since 4.19.1
     */
    private function loadListTables(): void
    {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/class-donor-table.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/class-donor-reports-table.php';
    }
}
