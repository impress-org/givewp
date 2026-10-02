<?php

namespace Give\Tests\Unit\Includes;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class MiscFunctionsFormMetaDispatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    protected function tearDown(): void
    {
        $completed = array_values(array_diff(
            (array)get_option('give_completed_upgrades', []),
            ['v20_move_metadata_into_new_table']
        ));
        update_option('give_completed_upgrades', $completed, false);

        parent::tearDown();
    }

    /**
     * When the v20 upgrade is complete and the post type is give_forms,
     * give_update_meta with no meta_type must write to wp_give_formmeta
     * and the row count must stay at 1 across multiple updates.
     *
     * @unreleased
     */
    public function testGiveUpdateMetaRoutesToFormmetaWhenUpgradeComplete()
    {
        global $wpdb;

        give_set_upgrade_complete('v20_move_metadata_into_new_table');

        $formId = self::factory()->post->create(['post_type' => 'give_forms']);
        $table = $wpdb->prefix . 'give_formmeta';

        give_update_meta($formId, '_give_form_sales', 5);
        give_update_meta($formId, '_give_form_sales', 7);
        give_update_meta($formId, '_give_form_sales', 9);

        $count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE form_id = %d AND meta_key = %s",
            $formId,
            '_give_form_sales'
        ));
        $this->assertSame(1, $count, 'Expected one row per (form_id, meta_key).');

        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$table} WHERE form_id = %d AND meta_key = %s",
            $formId,
            '_give_form_sales'
        ));
        $this->assertSame('9', $value, 'Expected in-place update with the latest value.');
    }

    /**
     * When the v20 upgrade has NOT run, give_update_meta must keep the
     * legacy behavior of writing to wp_postmeta.
     *
     * @unreleased
     */
    public function testGiveUpdateMetaFallsBackToPostmetaWhenUpgradeNotComplete()
    {
        $formId = self::factory()->post->create(['post_type' => 'give_forms']);

        give_update_meta($formId, '_give_form_earnings', 100);

        $value = get_post_meta($formId, '_give_form_earnings', true);
        $this->assertSame('100', (string)$value, 'Expected wp_postmeta write on legacy installs.');
    }
}
