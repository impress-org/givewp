<?php

namespace Give\Tests\Unit\DonationForms\Migrations;

use Give\DonationForms\Migrations\FormStatMetaDedupeAndIndex;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class FormStatMetaDedupeAndIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDuplicateStatMetaRowsAreCollapsed()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_formmeta';
        $formId = self::factory()->post->create(['post_type' => 'give_forms']);

        $statKeys = [
            '_give_form_earnings',
            '_give_form_sales',
            '_give_form_goal_progress',
        ];

        // Insert three duplicates per key with increasing meta_id.
        foreach ($statKeys as $key) {
            foreach ([10, 20, 30] as $value) {
                $wpdb->insert(
                    $table,
                    [
                        'form_id'    => $formId,
                        'meta_key'   => $key,
                        'meta_value' => (string)$value,
                    ],
                    ['%d', '%s', '%s']
                );
            }
        }

        // Pre-condition: 9 rows for this form.
        $count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE form_id = %d",
            $formId
        ));
        $this->assertSame(9, $count);

        // Act
        (new FormStatMetaDedupeAndIndex())->run();

        // Post-condition: one row per (form_id, meta_key), the newest value (30).
        foreach ($statKeys as $key) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_value FROM {$table} WHERE form_id = %d AND meta_key = %s",
                $formId,
                $key
            ));
            $this->assertCount(1, $rows, "Expected one row for {$key}.");
            $this->assertSame('30', $rows[0]->meta_value);
        }
    }

    /**
     * @unreleased
     */
    public function testCompositeIndexIsAdded()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_formmeta';

        // Drop the index if it pre-exists so the migration has to add it.
        // Swallow "doesn't exist" on a fresh DB so the test still passes.
        $wpdb->query("ALTER TABLE {$table} DROP INDEX form_id_meta_key");

        (new FormStatMetaDedupeAndIndex())->run();

        $hasIndex = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW INDEX FROM {$table} WHERE Key_name = %s",
                'form_id_meta_key'
            )
        );

        $this->assertNotNull($hasIndex);
    }

    /**
     * @unreleased
     */
    public function testMigrationIsIdempotent()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_formmeta';
        $formId = self::factory()->post->create(['post_type' => 'give_forms']);

        // Two duplicate rows.
        foreach ([1, 2] as $value) {
            $wpdb->insert(
                $table,
                [
                    'form_id'    => $formId,
                    'meta_key'   => '_give_form_earnings',
                    'meta_value' => (string)$value,
                ],
                ['%d', '%s', '%s']
            );
        }

        $migration = new FormStatMetaDedupeAndIndex();
        $migration->run();
        $migration->run(); // Re-run must not error and must not change state.

        $count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE form_id = %d",
            $formId
        ));
        $this->assertSame(1, $count);
    }
}
