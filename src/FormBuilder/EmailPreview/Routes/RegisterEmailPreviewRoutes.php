<?php

namespace Give\FormBuilder\EmailPreview\Routes;

class RegisterEmailPreviewRoutes {
    /**
     * @unreleased
     */
    public function __invoke()
    {
        foreach (include __DIR__ . '/routes.php' as $route => $args) {
            register_rest_route('givewp/form-builder/email-preview', $route, $args);
        }
    }
}