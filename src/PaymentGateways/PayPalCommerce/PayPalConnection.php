<?php

namespace Give\PaymentGateways\PayPalCommerce;

class PayPalConnection
{
    /**
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(
            'give-paypal-donation-settings',
            '/connect',
            [
                'methods' => 'GET',
                'callback' => [$this, 'renderConnectButton'],
                'permission_callback' => '__return_true',
                'args' => [
                    'action' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['paypal-live-connect', 'paypal-sandbox-connect']
                    ],
                ],
            ]
        );

        add_filter( 'rest_pre_serve_request', function( $served, $result, \WP_REST_Request $request) {
            if( $request->get_route() === '/give-paypal-donation-settings/connect' ) {
                echo $result->data;

                return true;
            }

            return $served;
        }, 10, 3);
    }

    /**
     * @return bool
     */
    public function routeAccessPermission(): bool
    {
        return current_user_can('manage_give_settings');
    }

    /**
     * @return \WP_REST_Response
     */
    public function renderConnectButton(\WP_REST_Request $request): \WP_REST_Response
    {
        $action = $request->get_param('action');

        ob_start();

        switch ($action) {
            case 'paypal-live-connect':
                $this->renderLiveConnectButton();
                break;

            case 'paypal-sandbox-connect':
                $this->renderSandboxConnectButton();
                break;
        }

        $response = new \WP_REST_Response( ob_get_clean() );
        $response->set_status( 200 );
        $response->header( 'Content-Type', 'text/html' );

        return $response;
    }

    /**
     * @return void
     */
    protected function renderLiveConnectButton()
    {
        echo $this->connectButtonTemplate('Connect with PayPal Live');
    }

    /**
     * @return void
     */
    protected function renderSandboxConnectButton()
    {
        echo $this->connectButtonTemplate('Connect with PayPal Sandbox');
    }

    /**
     * @param $buttonTitle
     *
     * @return string
     */
    private function connectButtonTemplate($buttonTitle): string
    {
        ob_start()
        ?>
        <!doctype html>
        <html <?php language_attributes(); ?>>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport"
                      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>%1$s</title>
            </head>
            <body>
                <button class="button button-primary button-large" id="js-give-paypal-on-boarding-handler">
                    <i class="fab fa-paypal"></i>&nbsp;&nbsp;%1$s
                </button>
                <a class="give-hidden" target="_blank"
                   data-paypal-onboard-complete="givePayPalOnBoardedCallback" href="#"
                   data-paypal-button="true"><?php esc_html_e('Sign up for PayPal', 'give'); ?>
                </a>
            </body>
        </html>
        <?php
        return sprintf(
            ob_get_clean(),
            $buttonTitle
        );
    }
}
