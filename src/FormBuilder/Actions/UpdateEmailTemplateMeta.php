<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * Update email template options on backwards compatible form meta.
 *
 * @since 3.0.0
 */
class UpdateEmailTemplateMeta
{
    /**
     * @since 3.0.0
     * @param  DonationForm  $form
     */
    public function __invoke(DonationForm $form)
    {
        foreach($form->settings->emailTemplateOptions as $emailType => $templateOptions) {

            $templateOptions['notification'] = $templateOptions['status'];
            unset($templateOptions['status']);

            if(isset($templateOptions['recipient'])) {
                $templateOptions['recipient'] = $this->formatRecipientEmails($templateOptions['recipient']);
            }

            foreach($templateOptions as $key => $value) {
                give()->form_meta->update_meta($form->id, "_give_{$emailType}_{$key}", $value);
            }
        }
    }

    /**
     * @since 3.0.0
     * @param  array  $emails
     * @return array
     */
    protected function formatRecipientEmails($emails): array
    {
        return array_map(function($email) {
            return ['email' => $email];
        }, $emails);
    }
}
