<?php

namespace Give\FormBuilder\EmailPreview\Controllers;

use Give\FormBuilder\EmailPreview\Actions\BuildEmailPreview;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Send email preview.
 *
 * @since 3.0.0
 */
class SendEmailPreview
{
    /**
     * @since 3.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function __invoke(WP_REST_Request $request): WP_REST_Response
    {
        $email = $request->get_param('email_address');
        $subject = $request->get_param('email_subject');
        $message = give(BuildEmailPreview::class)->__invoke($request);
        $headers = 'From:' . $email . "\r\n" .
                   'Content-Type: text/html; charset=UTF-8' . "\r\n";

        $sent = wp_mail($email, $subject, $message, $headers);

        ob_clean();

        return new WP_REST_Response($sent, 200);
    }
}
