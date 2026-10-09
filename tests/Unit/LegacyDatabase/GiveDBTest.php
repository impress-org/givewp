<?php

namespace Give\Tests\Unit\LegacyDatabase;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_DB_Donors;

/**
 * Covers the query helpers of the Give_DB base class, using the donors table class.
 *
 * The get_results_by() test with a quote in the value was added after the SQL fix.
 * Before the fix, the quote broke the SQL, so it could not run on the old code.
 *
 * @since TBD
 */
class GiveDBTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Give_DB_Donors is a Give_DB subclass. Give()->donors is a proxy around it, so a fresh
     * instance is used to make sure the base class methods run.
     *
     * @since TBD
     */
    private function db(): Give_DB_Donors
    {
        return new Give_DB_Donors();
    }

    /**
     * @since TBD
     */
    private function addDonor(string $name, string $email): int
    {
        $id = $this->db()->insert(
            [
                'name' => $name,
                'email' => $email,
                'date_created' => current_time('mysql'),
            ],
            'donor'
        );

        $this->assertGreaterThan(0, $id);

        return (int)$id;
    }

    /**
     * @since TBD
     */
    public function testGetReturnsRowByPrimaryKey()
    {
        $id = $this->addDonor('Jane Doe', 'jane-get@example.org');

        $row = $this->db()->get($id);

        $this->assertSame($id, (int)$row->id);
        $this->assertSame('jane-get@example.org', $row->email);
    }

    /**
     * @since TBD
     */
    public function testGetByReturnsRowByColumn()
    {
        $id = $this->addDonor('Jane Doe', 'jane-by@example.org');

        $row = $this->db()->get_by('email', 'jane-by@example.org');

        $this->assertSame($id, (int)$row->id);
    }

    /**
     * @since TBD
     */
    public function testGetColumnReturnsValueByPrimaryKey()
    {
        $id = $this->addDonor('Column Donor', 'column@example.org');

        $this->assertSame('Column Donor', $this->db()->get_column('name', $id));
    }

    /**
     * @since TBD
     */
    public function testGetColumnByReturnsValueByColumn()
    {
        $id = $this->addDonor('Column By Donor', 'column-by@example.org');

        $this->assertSame((string)$id, $this->db()->get_column_by('id', 'email', 'column-by@example.org'));
    }

    /**
     * @since TBD
     */
    public function testGetResultsByMatchesAllColumnsWithAnd()
    {
        $id = $this->addDonor('Ann Lee', 'ann@example.org');
        $this->addDonor('Ann Lee', 'other-ann@example.org');

        $rows = $this->db()->get_results_by(['name' => 'Ann Lee', 'email' => 'ann@example.org']);

        $this->assertCount(1, $rows);
        $this->assertSame($id, (int)$rows[0]->id);
    }

    /**
     * @since TBD
     */
    public function testGetResultsByMatchesAnyColumnWithOr()
    {
        $this->addDonor('Or One', 'or-one@example.org');
        $this->addDonor('Or Two', 'or-two@example.org');

        $rows = $this->db()->get_results_by(
            ['name' => 'Or One', 'email' => 'or-two@example.org', 'relation' => 'OR']
        );

        $this->assertCount(2, $rows);
    }

    /**
     * Added after the SQL fix. Before the fix, the quote in the value broke the SQL.
     *
     * @since TBD
     */
    public function testGetResultsByFindsValueWithQuote()
    {
        global $wpdb;

        $id = $this->addDonor("Pat O'Brien", 'obrien@example.org');

        $rows = $this->db()->get_results_by(['name' => "Pat O'Brien"]);

        $this->assertSame('', $wpdb->last_error);
        $this->assertCount(1, $rows);
        $this->assertSame($id, (int)$rows[0]->id);
    }

    /**
     * Added after the SQL fix. The relation must be AND or OR, so it cannot carry SQL.
     *
     * @since TBD
     */
    public function testGetResultsByIgnoresInvalidRelation()
    {
        global $wpdb;

        $this->addDonor('Rel One', 'rel-one@example.org');

        $rows = $this->db()->get_results_by(
            ['name' => 'Rel One', 'email' => 'nobody@example.org', 'relation' => '1=1 OR']
        );

        $this->assertSame('', $wpdb->last_error);
        $this->assertCount(0, $rows);
    }

    /**
     * @since TBD
     */
    public function testDeleteRemovesRow()
    {
        $id = $this->addDonor('Delete Me', 'delete@example.org');

        // Give_DB_Donors overrides delete(), so call the Give_DB version on purpose.
        $deleteFromBase = function ($rowId) {
            return parent::delete($rowId);
        };

        $this->assertTrue($deleteFromBase->call($this->db(), $id));
        $this->assertNull($this->db()->get($id));
    }

    /**
     * @since TBD
     */
    public function testTableExists()
    {
        global $wpdb;

        $this->assertTrue($this->db()->table_exists($wpdb->donors));
        $this->assertFalse($this->db()->table_exists('nope'));
    }
}
