<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Helpers\LocationList;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use WP_REST_Request;

/**
 * @since 2.10.0
 */
class LocationRoute extends RouteAbstract
{

    /** @var string */
    public function endpoint()
    {
        return 'location';
    }

    public function args()
    {
        return [
            'countryCode' => [
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    /**
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        return [
            'states' => LocationList::getStates(
                $request->get_param('countryCode')
            ),
        ];
    }
}
