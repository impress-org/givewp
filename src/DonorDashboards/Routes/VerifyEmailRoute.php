<?php

namespace Give\DonorDashboards\Routes;

use Give\API\RestRoute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.10.0
 */
class VerifyEmailRoute implements RestRoute
{

    use Captcha\ProtectedRoute;

    /** @var string */
    protected $endpoint = 'donor-dashboard/verify-email';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'email' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'g-recaptcha-response' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                ],
            ]
        );
    }

    /**
     * Handles login request
     *
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        if ( ! $this->validateRecaptcha(
            $request->get_param('g-recaptcha-response'),
            $request,
            'g-recaptcha-response'
        )) {
            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'error',
                    'body_response' => [
                        'error' => 'email_failed',
                        'message' => esc_html__('Unable to send email. Please try again.', 'give'),
                    ],
                ]
            );
        }

        Give()->email_access->init();

        $email = $request->get_param('email');

        $donor = Give()->donors->get_donor_by('email', give_clean($email));

        if ($donor && Give()->email_access->can_send_email($donor->id)) {
            $sent = Give()->email_access->send_email($donor->id, $donor->email);

            if ($sent === true) {
                $sentMessage = (string)apply_filters(
                    'give_email_access_mail_send_notice',
                    esc_html__(
                        'Email sent. If not received, make sure it is a valid donor email.',
                        'give'
                    )
                );

                return new WP_REST_Response(
                    [
                        'status' => 200,
                        'response' => 'success',
                        'body_response' => [
                            'message' => $sentMessage,
                        ],
                    ]
                );
            } else {
                $failedMessage = esc_html__('Unable to send email. Please try again.', 'give');

                return new WP_REST_Response(
                    [
                        'status' => 400,
                        'response' => 'error',
                        'body_response' => [
                            'error' => 'email_failed',
                            'message' => $failedMessage,
                        ],
                    ]
                );
            }
        } else {
            $value = Give()->email_access->verify_throttle / 60;
            $spamMessage = (string)apply_filters(
                'give_email_access_requests_exceed_notice',
                sprintf(
                    esc_html__('Email sent. If not received, make sure it is a valid donor email.', 'give'),
                    sprintf(_n('%s minute', '%s minutes', $value, 'give'), $value)
                ),
                $value
            );

            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'error',
                    'body_response' => [
                        'message' => $spamMessage,
                    ],
                ]
            );
        }
    }
}
