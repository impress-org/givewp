<?php

namespace Give\Tests\Unit\Exports;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Exports\DonorsExport;
use Give\Tests\TestCase;

final class DonorsExportTest extends TestCase
{
    /** @test */
    public function it_includes_custom_csv_columns()
    {
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';
        $exporter = new DonorsExport();
        $exporter->set_properties([
            'giveDonorExport-startDate' => '',
            'giveDonorExport-endDate' => '',
            'searchBy' => '',
            'forms' => 0,
            'give_export_columns' => [
                'full_name' => 'on',
                'custom-column' => 'on', // Custom column in the request
            ],
        ]);

        Donor::factory()->create();

        add_filter( 'give_export_donors_get_default_columns', static function($columnData) {
            $columnData['custom-column'] = 'My Custom Column';
            return $columnData;
        });

        add_filter( 'give_export_get_data_donors', static function($data) {
            foreach($data as $key => $value) {
                $data[$key]['custom-column'] = 'My Custom Value';
            }
            return $data;
        });

        $exportData = $exporter->get_data();

        $this->assertArrayHasKey('custom-column', $exporter->csv_cols());
        $this->assertEquals('My Custom Value', $exportData[0]['custom-column']);
    }
}
