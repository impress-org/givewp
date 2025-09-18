<?php

namespace Give\Tests\Unit\LegacyTools\Importer {

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestGiveImportSubscriptions extends TestCase
{
    use RefreshDatabase;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-subscriptions.php';
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/import-functions.php';

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        // Reset globals modified in tests
        unset($_REQUEST['mapto'], $_REQUEST['step']);
    }

    /**
     * @unreleased
     */
    public function testRequiredFieldsValidationPassesAndFails(): void
    {
        $importer = \Give_Import_Subscriptions::get_instance();

        // Missing required field should fail
        $_REQUEST['mapto'] = [ 'form_id', 'period', 'frequency', 'amount', 'status' ];
        $this->assertFalse($importer->check_for_dropdown_or_import());

        // Include all required -> passes
        $_REQUEST['mapto'] = [ 'form_id', 'donor_id', 'period', 'frequency', 'amount', 'status' ];
        $this->assertTrue($importer->check_for_dropdown_or_import());
    }

    /**
     * @unreleased
     * @return void
     */
    public function testDropdownAutoselectsSnakeCaseHeaders(): void
    {
        $importer = \Give_Import_Subscriptions::get_instance();
        $options = give_import_subscription_options();

        // form_id should auto-select form_id
        $selectedOptions = [];
        ob_start();
        $importer->get_dropdown_option_html($options, '', 'form_id', $selectedOptions);
        $html = ob_get_clean();
        $this->assertStringContainsString('value="form_id" selected', $html);

        // fee_amount_recovered should auto-select fee_amount_recovered
        $selectedOptions = [];
        ob_start();
        $importer->get_dropdown_option_html($options, '', 'fee_amount_recovered', $selectedOptions);
        $html = ob_get_clean();
        $this->assertStringContainsString('value="fee_amount_recovered" selected', $html);
    }

    /**
     * @unreleased
     */
    public function testDropdownAutoselectsNormalizedHeaders(): void
    {
        $importer = \Give_Import_Subscriptions::get_instance();
        $options = give_import_subscription_options();

        // "Gateway Subscription ID" should match gateway_subscription_id via normalization
        $selectedOptions = [];
        ob_start();
        $importer->get_dropdown_option_html($options, '', 'Gateway Subscription ID', $selectedOptions);
        $html = ob_get_clean();
        $this->assertStringContainsString('value="gateway_subscription_id" selected', $html);
    }

    /**
     * @unreleased
     */
        public function testGetStepLogicWithAndWithoutMapto(): void
    {
        $importer = \Give_Import_Subscriptions::get_instance();

        // With step=2 but missing required -> remains on step 2
        $_REQUEST['step'] = 2;
        $_REQUEST['mapto'] = [ 'form_id', 'period', 'frequency', 'amount', 'status' ];
        $this->assertSame(2, $importer->get_step());

        // With all required present -> goes to step 3
        $_REQUEST['step'] = 2;
        $_REQUEST['mapto'] = [ 'form_id', 'donor_id', 'period', 'frequency', 'amount', 'status' ];
        $this->assertSame(3, $importer->get_step());
    }

    /**
     * @unreleased
     */
    public function testImportsFromCsvCreatesSubscriptions(): void
    {
        // Create a donation form using model factory
        /** @var \Give\DonationForms\Models\DonationForm $form */
        $form = \Give\DonationForms\Models\DonationForm::factory()->create();
        $formId = $form->id;

        // Build a temp CSV from fixture by injecting runtime form id
        $fixture = __DIR__ . '/fixtures/subscriptions.csv';
        $this->assertFileExists($fixture);
        $contents = file_get_contents($fixture);
        $contents = str_replace('FORM_ID', (string) $formId, $contents);
        $tmpCsv   = tempnam(sys_get_temp_dir(), 'give-sub-import-');
        file_put_contents($tmpCsv, $contents);

        // Parse CSV
        $rawData  = give_get_raw_data_from_file($tmpCsv, 1, 100, ',');
        $mainKeyArr  = give_get_raw_data_from_file($tmpCsv, 0, 1, ',');
        $mainKey  = $mainKeyArr[0];
        $rawKey   = $mainKey; // header keys for array_combine

        // Import settings
        $import_setting = [
            'delimiter'   => 'csv',
            'mode'        => 0,
            'create_user' => 0,
            'delete_csv'  => 0,
            'dry_run'     => 0,
            'raw_key'     => $rawKey,
        ];

        give_import_subscription_report_reset();

        $rowKey = 1;
        foreach ($rawData as $row) {
            $import_setting['row_key'] = $rowKey;
            give_save_import_subscription_to_db($rawKey, $row, $mainKey, $import_setting);
            $rowKey++;
        }

        $report = give_import_subscription_report();
        $this->assertNotEmpty($report);
        $this->assertArrayHasKey('create_subscription', $report);
        $this->assertGreaterThanOrEqual(1, (int) $report['create_subscription']);
        $this->assertTrue(empty($report['failed_subscription']));
    }
}

}
