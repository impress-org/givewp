<?php

namespace Give\Campaigns\Routes;

use DateTime;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class CreateCampaign implements RestRoute
{
    /** @var string */
    protected $endpoint = 'campaigns';

    /**
     * @unreleased
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
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'shortDescription' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'image' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_url',
                    ],
                    'goalType' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'goal' => [
                        'type' => 'integer',
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'startDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
                        },
                    ],
                    'endDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
                        },
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $campaign = Campaign::create([
            'type' => CampaignType::CORE(),
            'title' => $request->get_param('title') ?? '',
            'shortDescription' => $request->get_param('shortDescription') ?? '',
            'longDescription' => '',
            'logo' => '',
            'image' => $request->get_param('image') ?? '',
            'primaryColor' => '',
            'secondaryColor' => '',
            //'goalType' => $request->get_param('goalType') ?? '',
            'goal' => $request->get_param('goal') ?? '',
            'status' => CampaignStatus::DRAFT(),
            'startDate' => $request->get_param('startDateTime') ?? null,
            'endDate' => $request->get_param('endDateTime') ?? null,
        ]);

        return new WP_REST_Response($campaign->toArray(), 201);
    }
}
