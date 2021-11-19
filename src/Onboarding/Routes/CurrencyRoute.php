<?php

namespace Give\Onboarding\Routes;

use Give\API\RestRoute;
use Give\Onboarding\SettingsRepositoryFactory;
use WP_REST_Request;

/**
 * @since 2.8.0
 */
class CurrencyRoute implements RestRoute
{

    /** @var string */
    protected $endpoint = 'onboarding/settings/currency';

    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @since 2.8.0
     *
     * @param SettingsRepository $settingsRepository
     *
     */
    public function __construct(SettingsRepositoryFactory $settingsRepositoryFactory)
    {
        $this->settingsRepository = $settingsRepositoryFactory->make('give_settings');
    }

    /**
     * @since 2.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $currencyCode = json_decode($request->get_param('value'));

        $currencyList = give_get_currencies_list();
        $currencyConfiguration = $currencyList[$currencyCode]['setting'];

        $this->settingsRepository->set('currency', $currencyCode);
        $this->settingsRepository->set('currency_position', $currencyConfiguration['currency_position']);
        $this->settingsRepository->set('thousands_separator', $currencyConfiguration['thousands_separator']);
        $this->settingsRepository->set('decimal_separator', $currencyConfiguration['decimal_separator']);
        $this->settingsRepository->set('number_decimals', $currencyConfiguration['number_decimals']);
        $this->settingsRepository->save();

        return [
            'data' => [
                'setting' => 'currency',
                'value' => $currencyCode,
            ],
        ];
    }

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
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                    'args' => [
                        'value' => [
                            'type' => 'string',
                            'required' => true,
                            // 'validate_callback' => [ $this, 'validateSetting' ],
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @since 2.8.0
     * @return array
     *
     */
    public function getSchema()
    {
        return [
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title' => 'onboarding',
            'type' => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => [
                'currencyCode' => [
                    'description' => esc_html__('Two letter code representing a country.', 'give'),
                    'type' => 'string',
                ],
            ],
        ];
    }
}
