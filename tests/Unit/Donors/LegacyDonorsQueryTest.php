<?php

namespace Give\Tests\Unit\Donors;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache;
use Give_Donors_Query;

/**
 * Covers the queries of Give_Donors_Query.
 *
 * The tests for several forms, a form list with non-numeric ids, an unknown fields value and an
 * invalid compare value were added after the SQL fix, because they check the new behavior of the
 * form filter and the allowlists. Every other test was written first and passed on the unchanged
 * code.
 *
 * @since TBD
 */
class LegacyDonorsQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // The query results are cached by SQL text, and the tables are emptied between tests.
        Give_Cache::flush_cache(true);
    }

    /**
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
     */
    public function testSearchByNameMatchesPartOfTheName()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Bobby Tables'], $this->names(['s' => 'Bobby']));
    }

    /**
     * @since TBD
     */
    public function testSearchWithNamePrefixMatchesPartOfTheName()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Alice Smith'], $this->names(['s' => 'name:Alic']));
    }

    /**
     * @since TBD
     */
    public function testSearchByEmailMatchesPartOfTheEmail()
    {
        $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Alice Smith'], $this->names(['s' => 'alice@example.org']));
    }

    /**
     * @since TBD
     */
    public function testSearchByNumericValueMatchesTheDonorId()
    {
        $bobby = $this->makeDonor('Bobby Tables', 'bobby@example.org');
        $this->makeDonor('Alice Smith', 'alice@example.org');

        $this->assertSame(['Bobby Tables'], $this->names(['s' => (string)$bobby->id]));
    }

    /**
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
}
