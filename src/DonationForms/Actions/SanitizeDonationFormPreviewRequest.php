<?php

namespace Give\DonationForms\Actions;

class SanitizeDonationFormPreviewRequest
{
    /**
     * @since 0.6.0
     */
    public function __invoke($var)
    {
        if (is_array($var)) {
            return array_map($this, $var);
        } else {
            return is_string($var) ? wp_kses_post(wp_unslash($var)) : $var;
        }
    }
}
