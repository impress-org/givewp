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
     * @var SettingsRepositoryFactory
     */
    protected $settingsRepository;

    /**
     * @since 2.8.0
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
                            'validate_callback' => [$this, 'validateSetting'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Limits the symbol to a 3-letter currency code
     *
     * @since 2.21.3
     */
    public function validateSetting($value): bool
    {
        return array_key_exists(json_decode($value, false), give_get_currencies_list());
    }
}
