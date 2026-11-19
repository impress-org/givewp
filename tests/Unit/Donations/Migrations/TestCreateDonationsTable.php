<?php

namespace Give\Tests\Unit\Donations\Migrations;

use Give\Donations\Migrations\CreateDonationsTable;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TestCreateDonationsTable extends TestCase
{
    /**
     * @since TBD
     */
    public function testCreatesTableWithThirtyFourColumns()
    {
        $table = DB::prefix('give_donations');
        DB::query("DROP TABLE IF EXISTS {$table}");

        (new CreateDonationsTable())->run();

        $columns = DB::get_col("SHOW COLUMNS FROM {$table}");

        $this->assertCount(34, $columns);
        $this->assertSame('id', $columns[0]);
        $this->assertContains('amount_intended', $columns);
        $this->assertContains('amount_intended_base', $columns);
    }

    /**
     * @since TBD
     */
    public function testGeneratedColumnsDeriveIntendedAmounts()
    {
        $table = DB::prefix('give_donations');
        (new CreateDonationsTable())->run();

        DB::query("INSERT INTO {$table}
            (id, donor_id, status, type, mode, gateway_id, amount_total, fee_amount_recovered, currency, base_currency, exchange_rate, email, first_name, last_name, created_at, updated_at)
            VALUES (1, 1, 'publish', 'single', 'live', 'manual', 10300, 300, 'EUR', 'USD', 0.5, 'a@b.c', 'A', 'B', NOW(), NOW())");

        $row = DB::get_row("SELECT amount_intended, amount_intended_base FROM {$table} WHERE id = 1");

        $this->assertSame(10000, (int)$row->amount_intended);
        $this->assertSame(20000, (int)$row->amount_intended_base);

        DB::query("DELETE FROM {$table} WHERE id = 1");
    }

    /**
     * Legacy storage accepts a total edited below the recovered fee, so the table must too.
     *
     * @since TBD
     */
    public function testIntendedAmountCanGoNegative()
    {
        $table = DB::prefix('give_donations');
        (new CreateDonationsTable())->run();

        DB::query("INSERT INTO {$table}
            (id, donor_id, status, type, mode, gateway_id, amount_total, fee_amount_recovered, currency, base_currency, email, first_name, last_name, created_at, updated_at)
            VALUES (2, 1, 'publish', 'single', 'live', 'manual', 100, 300, 'USD', 'USD', 'a@b.c', 'A', 'B', NOW(), NOW())");

        $this->assertSame(-200, (int)DB::get_var("SELECT amount_intended_base FROM {$table} WHERE id = 2"));

        DB::query("DELETE FROM {$table} WHERE id = 2");
    }

    /**
     * @since TBD
     */
    public function testIsIdempotent()
    {
        $table = DB::prefix('give_donations');

        (new CreateDonationsTable())->run();
        (new CreateDonationsTable())->run();

        $this->assertCount(34, DB::get_col("SHOW COLUMNS FROM {$table}"));
    }
}
