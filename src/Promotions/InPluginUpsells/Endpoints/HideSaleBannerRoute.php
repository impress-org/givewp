<?php

namespace Give\Promotions\InPluginUpsells\Endpoints;

use Give\API\RestRoute;
use Give\Promotions\InPluginUpsells\SaleBanners;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.17.0
 */
class HideSaleBannerRoute implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint = 'sale-banner/hide';

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
                    'permission_callback' => 'is_user_logged_in',
                    'args' => [
                        'id' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        give(SaleBanners::class)->hideBanner(
            $request->get_param('id') . get_current_user_id()
        );

        return new WP_REST_Response();
    }

}
