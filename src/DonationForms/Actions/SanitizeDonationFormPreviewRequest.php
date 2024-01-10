<?php

namespace Give\DonationForms\Actions;

class SanitizeDonationFormPreviewRequest
{
    /**
     * @since 3.0.0
     */
    public function __invoke($var)
    {
        if (is_array($var)) {
            return array_map($this, $var);
        } else {
            return is_string($var) ? wp_unslash($var) : $var;
        }
    }
}
