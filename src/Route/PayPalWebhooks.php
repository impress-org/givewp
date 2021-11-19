<?php

namespace Give\Route;

use Give\Controller\PayPalWebhooks as Controller;

class PayPalWebhooks implements Route
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        add_action('wp', [$this, 'callController']);
    }

    /**
     * Calls the corresponding controller for the route in the appropriate context
     *
     * @since 2.8.0
     */
    public function callController()
    {
        if (isset($_GET['give-listener']) && $_GET['give-listener'] === 'paypal-commerce') {
            give(Controller::class)->handle();

            http_response_code(200);
            die();
        }
    }

    /**
     * Returns the route URL
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function getRouteUrl()
    {
        return get_site_url(null, 'index.php?give-listener=paypal-commerce', 'https');
    }
}
