<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class PdfSettings extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process()
    {
        $oldFormId = $this->formV2->id;
        $newForm = $this->formV3;

        $pdfSettings = [
            'enable' => $this->getValue($oldFormId, 'give_pdf_receipts_enable_disable', 'global'),
            'generationMethod' => $this->getValue($oldFormId, 'give_pdf_generation_method', 'set_pdf_templates'),
            'colorPicker' => $this->getValue($oldFormId, 'give_pdf_colorpicker', '#1E8CBE'),
            'templateId' => $this->getValue($oldFormId, 'give_pdf_templates', 'default'),
            'logoUpload' => $this->getValue($oldFormId, 'give_pdf_logo_upload', ''),
            'name' => $this->getValue($oldFormId, 'give_pdf_company_name', ''),
            'addressLine1' => $this->getValue($oldFormId, 'give_pdf_address_line1', ''),
            'addressLine2' => $this->getValue($oldFormId, 'give_pdf_address_line2', ''),
            'cityStateZip' => $this->getValue($oldFormId, 'give_pdf_address_city_state_zip', ''),
            'displayWebsiteUrl' => $this->getValue($oldFormId, 'give_pdf_url', ''),
            'emailAddress' => $this->getValue($oldFormId, 'give_pdf_email_address', ''),
            'headerMessage' => $this->getValue($oldFormId, 'give_pdf_header_message', ''),
            'footerMessage' => $this->getValue($oldFormId, 'give_pdf_footer_message', ''),
            'additionalNotes' => $this->getValue($oldFormId, 'give_pdf_additional_notes', ''),
            'customTemplateId' => $this->getValue($oldFormId, 'give_pdf_receipt_template', ''),
            'customTemplateName' => $this->getValue($oldFormId, 'give_pdf_receipt_template_name', ''),
            'customPageSize' => $this->getValue($oldFormId, 'give_pdf_builder_page_size', ''),
            'customPdfBuilder' => $this->getValue($oldFormId, 'give_pdf_builder', ''),
        ];

        $newForm->settings->pdfSettings = $pdfSettings;
        $newForm->save();
    }

    /**
     * @unreleased
     */
    private function getValue(int $formId, string $metaKey, $defaultValue)
    {
        $metaValue = give()->form_meta->get_meta($formId, $metaKey, true);

        if ( ! $metaValue) {
            return $defaultValue;
        }

        return $metaValue;
    }
}
