<?php

namespace Give\DonorDashboards\Routes\Captcha;

/**
 * Note: Functionality forked from `give_email_access_login()`.
 *
 * @since 2.10.2
 */
trait ProtectedRoute
{

    /**
     * @since 2.10.2
     *
     * @return bool
     */
    public function isCaptchaEnabled()
    {
        $recaptcha_key = give_get_option('recaptcha_key');
        $recaptcha_secret = give_get_option('recaptcha_secret');
        $recaptcha_enabled = (give_is_setting_enabled(
            give_get_option('enable_recaptcha')
        )) && ! empty($recaptcha_key) && ! empty($recaptcha_secret) ? true : false;

        return $recaptcha_enabled;
    }

    /**
     * @since 2.10.2
     *
     * @param string $value
     * @param WP_REST_Request $request
     * @param string $param The name of the parameter.
     *
     * @return bool
     */
    public function validateRecaptcha($value, $request, $param)
    {
        if ( ! $this->isCaptchaEnabled()) {
            return true;
        }

        if ( ! $value) {
            return false;
        }

        $request = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body' => [
                    'secret' => give_get_option('recaptcha_secret'),
                    'response' => $value,
                    'remoteip' => $request->get_param('give_ip'),
                ],
            ]
        );

        if (is_wp_error($request)) {
            return false;
        }

        if (200 !== wp_remote_retrieve_response_code($request)) {
            return false;
        }

        $response = json_decode($request['body'], true);

        return ! ! $response['success'];
    }
}
