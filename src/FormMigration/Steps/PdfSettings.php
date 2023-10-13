<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.0.0
 */
class PdfSettings extends FormMigrationStep
{
    /**
     * @since 3.0.0
     */
    public function process()
    {
        $oldFormId = $this->formV2->id;
        $newForm = $this->formV3;

        $pdfSettings = [
            'enable' => $this->getMetaValue($oldFormId, 'give_pdf_receipts_enable_disable', 'global'),
            'generationMethod' => $this->getMetaValue($oldFormId, 'give_pdf_generation_method', 'set_pdf_templates'),
            'colorPicker' => $this->getMetaValue($oldFormId, 'give_pdf_colorpicker', '#1E8CBE'),
            'templateId' => $this->getMetaValue($oldFormId, 'give_pdf_templates', 'default'),
            'logoUpload' => $this->getMetaValue($oldFormId, 'give_pdf_logo_upload', ''),
            'name' => $this->getMetaValue($oldFormId, 'give_pdf_company_name', ''),
            'addressLine1' => $this->getMetaValue($oldFormId, 'give_pdf_address_line1', ''),
            'addressLine2' => $this->getMetaValue($oldFormId, 'give_pdf_address_line2', ''),
            'cityStateZip' => $this->getMetaValue($oldFormId, 'give_pdf_address_city_state_zip', ''),
            'displayWebsiteUrl' => $this->getMetaValue($oldFormId, 'give_pdf_url', ''),
            'emailAddress' => $this->getMetaValue($oldFormId, 'give_pdf_email_address', ''),
            'headerMessage' => $this->getMetaValue($oldFormId, 'give_pdf_header_message', ''),
            'footerMessage' => $this->getMetaValue($oldFormId, 'give_pdf_footer_message', ''),
            'additionalNotes' => $this->getMetaValue($oldFormId, 'give_pdf_additional_notes', ''),
            'customTemplateId' => $this->getMetaValue($oldFormId, 'give_pdf_receipt_template', ''),
            'customTemplateName' => $this->getMetaValue($oldFormId, 'give_pdf_receipt_template_name', ''),
            'customPageSize' => $this->getMetaValue($oldFormId, 'give_pdf_builder_page_size', ''),
            'customPdfBuilder' => $this->getMetaValue($oldFormId, 'give_pdf_builder', ''),
        ];

        $newForm->settings->pdfSettings = $pdfSettings;
    }

    /**
     * @since 3.0.0
     */
    private function getMetaValue(int $formId, string $metaKey, $defaultValue)
    {
        $metaValue = give()->form_meta->get_meta($formId, $metaKey, true);

        if ( ! $metaValue) {
            return $defaultValue;
        }

        return $metaValue;
    }
}
