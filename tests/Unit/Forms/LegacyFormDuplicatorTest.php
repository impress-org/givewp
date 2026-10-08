<?php

namespace Give\Tests\Unit\Forms;

use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Form_Duplicator;

/**
 * Covers the form meta queries of Give_Form_Duplicator.
 *
 * @since TBD
 */
final class LegacyFormDuplicatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php';
    }

    /**
     * @since TBD
     */
    private function metaOf(int $formId): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->formmeta} WHERE form_id = %d", $formId)
        );

        return wp_list_pluck($rows, 'meta_value', 'meta_key');
    }

    /**
     * @since TBD
     */
    public function testDuplicatedFormHasTheSameMetaAndResetStats()
    {
        $form = DonationForm::factory()->create();

        give_update_meta($form->id, '_give_test_custom_key', "O'Brien 100% sure");
        give_update_meta($form->id, '_give_test_other_key', 'second value');
        give_update_meta($form->id, '_give_form_sales', '7');
        give_update_meta($form->id, '_give_form_earnings', '123.45');

        $originalMeta = $this->metaOf($form->id);

        $duplicateId = Give_Form_Duplicator::handler($form->id);

        $this->assertNotFalse($duplicateId);
        $this->assertNotEquals($form->id, $duplicateId);

        $duplicateMeta = $this->metaOf($duplicateId);

        $this->assertSame("O'Brien 100% sure", $duplicateMeta['_give_test_custom_key']);
        $this->assertSame('second value', $duplicateMeta['_give_test_other_key']);
        $this->assertSame('0', $duplicateMeta['_give_form_sales']);
        $this->assertSame('0', $duplicateMeta['_give_form_earnings']);

        $expected = $originalMeta;
        $expected['_give_form_sales'] = '0';
        $expected['_give_form_earnings'] = '0';
        ksort($expected);
        ksort($duplicateMeta);

        $this->assertSame($expected, $duplicateMeta);

        // The original form keeps its own stats.
        $this->assertSame('7', $this->metaOf($form->id)['_give_form_sales']);
    }

    /**
     * @since TBD
     */
    public function testResetStatsFilterChangesWhichMetaKeysAreReset()
    {
        $form = DonationForm::factory()->create();

        give_update_meta($form->id, '_give_test_reset_me', 'keep until reset');

        add_filter('give_duplicate_form_reset_stat_meta_keys', static function ($keys) {
            $keys[] = '_give_test_reset_me';

            return $keys;
        });

        $duplicateId = Give_Form_Duplicator::handler($form->id);
        $duplicateMeta = $this->metaOf($duplicateId);

        $this->assertSame('0', $duplicateMeta['_give_test_reset_me']);
    }
}
