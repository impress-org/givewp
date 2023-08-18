<?php

namespace Give\FormBuilder\EmailPreview\Actions;

/**
 * Build email preview.
 *
 * @since 3.0.0
 */
class BuildEmailPreview
{
    /**
     * @var ApplyPreviewTemplateTags
     */
    protected $applyPreviewTemplateTagsAction;

    /**
     * @param  ApplyPreviewTemplateTags  $applyPreviewTemplateTagsAction
     */
    public function __construct(ApplyPreviewTemplateTags $applyPreviewTemplateTagsAction)
    {
        $this->applyPreviewTemplateTagsAction = $applyPreviewTemplateTagsAction;
    }

    /**
     * @param string $emailHeader
     * @return string
     */
    public function __invoke($request)
    {
        $formId = $request->get_param('form_id');
        $emailType = $request->get_param('email_type');

        /**
         * The $emailNotification object is maintained for filter backward compatibility.
         */
        try {
            /** @var \Give_Email_Notification $emailNotification */
            $emailNotification = give(GetEmailNotificationByType::class)->__invoke($emailType);
        } catch (\Exception $e) {
            return new \WP_REST_Response($e->getMessage(), 400);
        }

        $emailHeader = apply_filters("give_{$emailType}_get_email_header", $request->get_param('email_heading'), $emailNotification, $formId);
        $emailMessage = apply_filters("give_{$emailType}_get_email_message", $request->get_param('email_message'), $emailNotification, $formId);
        $emailTemplate = apply_filters("give_{$emailType}_get_email_template", $request->get_param('email_template'), $emailNotification, $formId);
        $contentType = apply_filters("give_{$emailType}_get_email_content_type", $request->get_param('email_content_type'), $emailNotification, $formId);

        Give()->emails->__set('html', 'text/html' === $contentType);
        Give()->emails->__set('content_type', $contentType);
        Give()->emails->__set('heading', $this->applyPreviewTemplateTags($emailHeader));
        Give()->emails->__set('template', 'text/html' === $contentType ? $emailTemplate : 'none');

        if('text/plain' === $contentType) {
            $emailMessage = wpautop($emailMessage);
        }

        add_filter('give_preview_email_receipt_header', '__return_false'); // Disable hard-coded preview switcher.
        do_action( "give_{$emailType}_email_preview", $emailNotification );

        return apply_filters( "give_{$emailType}_email_preview_message",
            Give()->emails->build_email($this->applyPreviewTemplateTags($emailMessage)),
            $email_preview_data = apply_filters( "give_{$emailType}_email_preview_data", array() ),
            $emailNotification
        );
    }

    /**
     * @param $emailHeader
     *
     * @return string
     */
    protected function applyPreviewTemplateTags($emailHeader): string
    {
        return $this->applyPreviewTemplateTagsAction->__invoke($emailHeader);
    }
}
