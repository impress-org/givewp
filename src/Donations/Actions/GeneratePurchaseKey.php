<?php

namespace Give\Donations\Actions;

class GeneratePurchaseKey {
    /**
     * @since 2.19.6
     *
     * @param string $email
     * @return string
     */
    public function __invoke($email)
    {
        $auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';

        return strtolower(md5($email . date('Y-m-d H:i:s') . $auth_key . uniqid('give', true)));
    }
}
