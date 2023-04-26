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
                    'mode' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => ['live', 'sandbox']
                    ],
                ],
            ]
        );

        add_filter('rest_pre_serve_request', function ($served, $result, \WP_REST_Request $request) {
            if ($request->get_route() === '/give-api/v2/paypal-connect-button') {
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
        $this->mode = $request->get_param('mode');

        ob_start();
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
                <title><?php echo $viewData['buttonTitle']; ?></title>
                <script src="<?php echo $viewData['partnerJsUrl']; ?>"></script>
                <script>
                    const ajaxurl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>';
                    function givePayPalOnBoardedCallback(authCode, sharedId) {
                        const query = '&authCode=' + authCode + '&sharedId=' + sharedId;
                        fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded' + query )
                            .then(function(res){ return res.json() })
                            .then(function(res) {
                                if ( true !== res.success ) {
                                    alert('Something went wrong!');
                                    return;
                                }

                                // Remove PayPal quick help container.
                                const paypalErrorQuickHelp = document.getElementById('give-paypal-onboarding-trouble-notice');
                                paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
                            });
                    }
                </script>
                <style>
                    .give-hidden {
                        display: none;
                    }
                    button{
                        cursor: pointer;
                    }
                </style>
            </head>
            <body>
                <button class="button button-primary button-large" id="js-give-paypal-on-boarding-handler">
                    <i class="fab fa-paypal"></i>&nbsp;&nbsp;<?php echo $viewData['buttonTitle']; ?>
                </button>
                <a class="give-hidden" target="PPFrame"
                   data-paypal-onboard-complete="givePayPalOnBoardedCallback" href="#"
                   data-paypal-button="true"><?php esc_html_e('Sign up for PayPal', 'give'); ?>
                </a>
                <script>
                    const onBoardingButton = document.getElementById('js-give-paypal-on-boarding-handler');

                    onBoardingButton.addEventListener( 'click', function( evt ) {
                        evt.preventDefault();
                        //removeErrors();

                        //const countryCode = countryField.value;
                        const countryCode = "<?php echo 'sandbox' === $this->mode ? 'CO' : 'US'; ?>";
                        const mode = "<?php echo $this->mode; ?>";
                        const buttonState = {
                            enable: () => {
                                onBoardingButton.disabled = false;
                                evt.target.innerText = onBoardingButton.getAttribute( 'data-initial-label' );
                            },
                            disable: () => {
                                // Preserve initial label.
                                if ( ! onBoardingButton.hasAttribute( 'data-initial-label' ) ) {
                                    onBoardingButton.setAttribute( 'data-initial-label', onBoardingButton.innerText );
                                }

                                onBoardingButton.disabled = true;
                                //evt.target.innerText = Give.fn.getGlobalVar( 'loader_translation' ).processing;
                                evt.target.innerText = 'Processing';
                            },
                        };

                        buttonState.disable();

                        // Hide PayPal quick help message.
                        //const paypalErrorQuickHelp = document.getElementById( 'give-paypal-onboarding-trouble-notice' );
                        //paypalErrorQuickHelp && paypalErrorQuickHelp.classList.add( 'give-hidden' );

                        fetch( ajaxurl + `?action=give_paypal_commerce_get_partner_url&countryCode=${ countryCode }&mode=${ mode }` )
                            .then( response => response.json() )
                            .then( function( res ) {
                                if ( true === res.success ) {
                                    const payPalLink = document.querySelector( '[data-paypal-button]' );

                                    payPalLink.href = `${ res.data.partnerLink }&displayMode=minibrowser`;
                                    payPalLink.click();

                                    // This object will check if a class added to body or not.
                                    // If class added that means modal opened.
                                    // If class removed that means modal closed.
                                    //paypalModalObserver.observe( document.querySelector( 'body' ), { attributes: true, childList: true } );
                                }

                                buttonState.enable();
                            } )
                            .then( function() {
                                fetch( ajaxurl + '?action=give_paypal_commerce_onboarding_trouble_notice' )
                                    .then( response => response.json() )
                                    .then( function( res ) {
                                        if ( true === res.success ) {
                                            function createElementFromHTML( htmlString ) {
                                                const div = document.createElement( 'div' );
                                                div.innerHTML = htmlString.trim();
                                                return div.firstChild;
                                            }

                                            const buttonContainer = document.querySelector( '.connect-button-wrap' );
                                            paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
                                            buttonContainer.append( createElementFromHTML( res.data ) );
                                        }
                                    } );
                            } );
                    } );
                </script>
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
