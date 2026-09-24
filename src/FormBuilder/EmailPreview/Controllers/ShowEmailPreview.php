<?php

namespace Give\FormBuilder\EmailPreview\Controllers;

use Give\FormBuilder\EmailPreview\Actions\BuildEmailPreview;

class ShowEmailPreview
{
    /**
     * @since TBD Escape output.
     */
    public function __invoke(\WP_REST_Request $request)
    {
        Give()->emails->__set(
            'html',
            true
        ); // Show formatted text in browser even text/plain content type set for an email.

        ob_clean();
        header('Content-Type: text/html; charset=UTF-8');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputs a complete rendered email, built from already-escaped parts.
        echo give(BuildEmailPreview::class)->__invoke($request);
        exit;
    }
}
