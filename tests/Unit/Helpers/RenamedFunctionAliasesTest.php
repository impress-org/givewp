<?php

namespace Give\Tests\Unit\Helpers;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Each renamed function has a give_-prefixed name. The old name must keep working with the same
 * result and log exactly one deprecation notice.
 *
 * @since TBD
 */
class RenamedFunctionAliasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string[]
     */
    private $notices = [];

    /**
     * @var array
     */
    private $deprecatedCalls = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->notices = [];
        $this->deprecatedCalls = [];

        add_filter('give_deprecated_function_trigger_error', '__return_true');
        add_action('give_deprecated_function_run', [$this, 'recordDeprecatedCall'], 10, 3);

        set_error_handler(function ($level, $message) {
            $this->notices[] = $message;

            return true;
        }, E_USER_NOTICE);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        restore_error_handler();
        remove_filter('give_deprecated_function_trigger_error', '__return_true');
        remove_action('give_deprecated_function_run', [$this, 'recordDeprecatedCall'], 10);

        // WordPress resets $post between tests, but not this one.
        unset($GLOBALS['thepostid']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function recordDeprecatedCall($function, $replacement, $version): void
    {
        $this->deprecatedCalls[] = [$function, $replacement, $version];
    }

    /**
     * Calls the old name, then asserts exactly one notice that names the new function.
     *
     * @since TBD
     *
     * @return mixed
     */
    private function callOldName(string $oldName, string $newName, array $args = [])
    {
        $this->notices = [];
        $this->deprecatedCalls = [];

        $result = $oldName(...$args);

        $this->assertCount(1, $this->notices, "$oldName() should log one notice");
        $this->assertStringContainsString("$oldName is <strong>deprecated</strong>", $this->notices[0]);
        $this->assertStringContainsString("Use $newName() instead.", $this->notices[0]);
        $this->assertCount(1, $this->deprecatedCalls);
        $this->assertSame([$oldName, "$newName()", 'TBD'], $this->deprecatedCalls[0]);

        return $result;
    }

    /**
     * Calls the new name and asserts it logs nothing.
     *
     * @since TBD
     *
     * @return mixed
     */
    private function callNewName(string $newName, array $args = [])
    {
        $this->notices = [];

        $result = $newName(...$args);

        $this->assertSame([], $this->notices, "$newName() should not log a notice");

        return $result;
    }

    /**
     * The donor meta functions use the `give_customer` meta type, which has no table in the test
     * database, so they return the same empty values before and after the rename. The test checks
     * that the old name returns exactly what the new name returns.
     *
     * @since TBD
     */
    public function testDonorMetaFunctions(): void
    {
        $donorId = Donor::factory()->create()->id;

        $calls = [
            ['add_donor_meta', 'give_add_donor_meta', [$donorId, 'key', 'value', true]],
            ['get_donor_meta', 'give_get_donor_meta', [$donorId, 'key', true]],
            ['get_donor_meta', 'give_get_donor_meta', [$donorId]],
            ['update_donor_meta', 'give_update_donor_meta', [$donorId, 'key', 'other', 'value']],
            ['delete_donor_meta', 'give_delete_donor_meta', [$donorId, 'key', 'other']],
        ];

        foreach ($calls as [$oldName, $newName, $args]) {
            $this->assertSame(
                $this->callNewName($newName, $args),
                $this->callOldName($oldName, $newName, $args)
            );
        }
    }

    /**
     * @since TBD
     */
    public function testLicenseFunctions(): void
    {
        $this->assertSame(
            $this->callNewName('give_get_platform_fee_from_licenses'),
            $this->callOldName('get_platform_fee_from_licenses', 'give_get_platform_fee_from_licenses')
        );
        $this->assertSame(
            $this->callNewName('give_get_active_license_date'),
            $this->callOldName('get_active_license_date', 'give_get_active_license_date')
        );
    }

    /**
     * @since TBD
     */
    public function testGetFormIdFromArgs(): void
    {
        $this->assertSame(12, $this->callNewName('give_get_form_id_from_args', [['form_id' => '12']]));
        $this->assertSame(12, $this->callOldName('get_form_id_from_args', 'give_get_form_id_from_args', [['form_id' => '12']]));
        $this->assertFalse($this->callOldName('get_form_id_from_args', 'give_get_form_id_from_args', [[]]));
    }

    /**
     * @since TBD
     */
    public function testGetPrefillFormFieldValues(): void
    {
        $new = $this->callNewName('give_get_prefill_form_field_values', [1]);

        $this->assertIsArray($new);
        $this->assertSame(
            $new,
            $this->callOldName('_give_get_prefill_form_field_values', 'give_get_prefill_form_field_values', [1])
        );
    }

    /**
     * @since TBD
     */
    public function testGetFormattedOfflineInstructions(): void
    {
        $args = ['Send a check to <strong>us</strong>.', 1, true];

        $new = $this->callNewName('give_get_formatted_offline_instructions', $args);

        $this->assertStringContainsString('Send a check', $new);
        $this->assertSame(
            $new,
            $this->callOldName('get_formatted_offline_instructions', 'give_get_formatted_offline_instructions', $args)
        );
    }

    /**
     * @since TBD
     */
    public function testMetaboxFormDataRepeaterFieldsOutputsTheSameMarkup(): void
    {
        if (! function_exists('give_metabox_form_data_repeater_fields')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/give-metabox-functions.php';
        }

        global $post, $thepostid;
        $post = get_post(self::factory()->post->create());
        $thepostid = $post->ID;

        $fields = [
            'id'     => 'repeater',
            'fields' => [
                ['id' => 'title', 'type' => 'text', 'name' => 'Title'],
            ],
        ];

        ob_start();
        $this->callNewName('give_metabox_form_data_repeater_fields', [$fields]);
        $new = ob_get_clean();

        ob_start();
        $this->callOldName('_give_metabox_form_data_repeater_fields', 'give_metabox_form_data_repeater_fields', [$fields]);
        $old = ob_get_clean();

        $this->assertStringContainsString('give-repeatable-field-section', $new);
        $this->assertSame($new, $old);
    }

    /**
     * @since TBD
     */
    public function testPaymentMetaBackwardCompatibilityFunctions(): void
    {
        $this->assertNull($this->callNewName('give_20_bc_split_and_save_give_payment_meta', [1, []]));
        $this->assertNull(
            $this->callOldName('_give_20_bc_split_and_save_give_payment_meta', 'give_20_bc_split_and_save_give_payment_meta', [1, []])
        );

        $meta = ['date' => '2020-01-01 00:00:00'];
        $new = $this->callNewName('give_20_bc_give_payment_meta_value', [999999, $meta]);

        $this->assertSame(
            $new,
            $this->callOldName('_give_20_bc_give_payment_meta_value', 'give_20_bc_give_payment_meta_value', [999999, $meta])
        );
    }
}
