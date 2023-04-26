<?php

namespace Give\PaymentGateways\PayPalCommerce;

class PayPalConnection
{
    /**
     * PayPal connection mode.
     * @var string
     */
    private $mode;

    /**
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(
            'give-api/v2',
            '/paypal-connect-button',
            [
                'methods' => 'GET',
                'callback' => [$this, 'renderConnectButton'],
                'permission_callback' => '__return_true',
                'args' => [
                    'action' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['live', 'sandbox']
                    ],
                ],
            ]
        );

        add_filter('rest_pre_serve_request', function ($served, $result, \WP_REST_Request $request) {
            if ($request->get_route() === '/give-paypal-donation-settings/connect') {
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

        $this->mode = 'sandbox';
        echo $this->renderButtonView();

        $response = new \WP_REST_Response(ob_get_clean());
        $response->set_status(200);
        $response->header('Content-Type', 'text/html');

        return $response;
    }

    /**
     * @param $buttonTitle
     *
     * @return string
     */
    private function renderButtonView(): string
    {
        $viewData = $this->buttonViewData();

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
                <script src="<?php echo $viewData['partnerJsUrl']; ?>">
            </head>
            <body>
                <button class="button button-primary button-large" id="js-give-paypal-on-boarding-handler">
                    <i class="fab fa-paypal"></i>&nbsp;&nbsp;<?php echo $viewData['buttonTitle']; ?>
                </button>
                <a class="give-hidden" target="_blank"
                   data-paypal-onboard-complete="givePayPalOnBoardedCallback" href="#"
                   data-paypal-button="true"><?php
                    esc_html_e('Sign up for PayPal', 'give'); ?>
                </a>
            </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * This function is used to get button view data.
     *
     * @unreleased
     *
     * @return array
     */
    private function buttonViewData(): array
    {
        $buttonTitle = 'sandbox' === $this->mode ?
            esc_html__( 'Connect with PayPal Sandbox', 'give' ) :
            esc_html__( 'Connect with PayPal Live', 'give' );

        $partnerJsUrl = $this->getPartnerJsUrl();

        return [
            'buttonTitle' => $buttonTitle,
            'partnerJsUrl' => $partnerJsUrl,
        ];
    }

    /**
     * Get PayPal partner js url.
     *
     * @since 2.9.0
     *
     * @return string
     */
    private function getPartnerJsUrl(): string
    {
        $baseUrl = sprintf(
            'https://%1$spaypal.com/',
            'sandbox' === $this->mode ? 'sandbox.' : ''
        );

        return sprintf(
            '%1$swebapps/merchantboarding/js/lib/lightbox/partner.js',
            $baseUrl
        );
    }
}
