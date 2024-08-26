<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\PdfSettings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\PdfSettings
 */
class TestPdfSettings extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testPdfSettingsProcess(): void
    {
        // Arrange
        $meta = [
            'give_pdf_receipts_enable_disable' => 'enabled',
            'give_pdf_generation_method' => 'auto',
            'give_pdf_colorpicker' => '#FF5733',
            'give_pdf_templates' => 'custom_template',
            'give_pdf_logo_upload' => 'logo.png',
            'give_pdf_company_name' => 'My Company',
            'give_pdf_address_line1' => '123 Main St',
            'give_pdf_address_line2' => 'Apt 4B',
            'give_pdf_address_city_state_zip' => 'New York, NY 10001',
            'give_pdf_url' => 'https://example.com',
            'give_pdf_email_address' => 'info@example.com',
            'give_pdf_header_message' => 'Thank you for your donation!',
            'give_pdf_footer_message' => 'Footer message here.',
            'give_pdf_additional_notes' => 'Additional notes...',
            'give_pdf_receipt_template' => 'custom_template',
            'give_pdf_receipt_template_name' => 'Custom Template Name',
            'give_pdf_builder_page_size' => 'A4',
            'give_pdf_builder' => 'custom_builder',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, PdfSettings::class);

        // Assert
        $this->assertEquals($meta['give_pdf_receipts_enable_disable'], $v3Form->settings->pdfSettings['enable']);
        $this->assertEquals($meta['give_pdf_generation_method'], $v3Form->settings->pdfSettings['generationMethod']);
        $this->assertEquals($meta['give_pdf_colorpicker'], $v3Form->settings->pdfSettings['colorPicker']);
        $this->assertEquals($meta['give_pdf_templates'], $v3Form->settings->pdfSettings['templateId']);
        $this->assertEquals($meta['give_pdf_logo_upload'], $v3Form->settings->pdfSettings['logoUpload']);
        $this->assertEquals($meta['give_pdf_company_name'], $v3Form->settings->pdfSettings['name']);
        $this->assertEquals($meta['give_pdf_address_line1'], $v3Form->settings->pdfSettings['addressLine1']);
        $this->assertEquals($meta['give_pdf_address_line2'], $v3Form->settings->pdfSettings['addressLine2']);
        $this->assertEquals($meta['give_pdf_address_city_state_zip'], $v3Form->settings->pdfSettings['cityStateZip']);
        $this->assertEquals($meta['give_pdf_url'], $v3Form->settings->pdfSettings['displayWebsiteUrl']);
        $this->assertEquals($meta['give_pdf_email_address'], $v3Form->settings->pdfSettings['emailAddress']);
        $this->assertEquals($meta['give_pdf_header_message'], $v3Form->settings->pdfSettings['headerMessage']);
        $this->assertEquals($meta['give_pdf_footer_message'], $v3Form->settings->pdfSettings['footerMessage']);
        $this->assertEquals($meta['give_pdf_additional_notes'], $v3Form->settings->pdfSettings['additionalNotes']);
        $this->assertEquals($meta['give_pdf_receipt_template'], $v3Form->settings->pdfSettings['customTemplateId']);
        $this->assertEquals($meta['give_pdf_receipt_template_name'], $v3Form->settings->pdfSettings['customTemplateName']);
        $this->assertEquals($meta['give_pdf_builder_page_size'], $v3Form->settings->pdfSettings['customPageSize']);
        $this->assertEquals($meta['give_pdf_builder'], $v3Form->settings->pdfSettings['customPdfBuilder']);
    }
}
