<?php

namespace Give\Onboarding\Routes;

use Give\API\RestRoute;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Onboarding\BlockFactory;
use Give\Onboarding\SettingsRepository;
use Give\Onboarding\SettingsRepositoryFactory;
use WP_REST_Request;

/**
 * @since 2.8.0
 */
class FeaturesRoute implements RestRoute
{

    /** @var string */
    protected $endpoint = 'onboarding/settings/features';

    /**
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @since 2.8.0
     *
     * @param SettingsRepositoryFactory $settingsRepositoryFactory
     */
    public function __construct(SettingsRepositoryFactory $settingsRepositoryFactory)
    {
        $this->settingsRepository = $settingsRepositoryFactory->make('give_onboarding');
    }

    /**
     * @inheritDoc
     */
    public function registerRoute(): void
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                    'args'                => [
                        'value' => [
                            'type'              => 'string',
                            'required'          => true,
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
    public function getSchema(): array
    {
        return [
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'      => 'onboarding',
            'type'       => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => [
                'setting' => [
                    'description' => esc_html__('The reference name for the setting being updated.', 'give'),
                    'type'        => 'string',
                ],
                'value'   => [
                    'description' => esc_html__('The value of the setting being updated.', 'give'),
                    'type'        => 'string',
                ],
            ],
        ];
    }

    /**
     * @since 3.15.0 Handle v3 form features.
     * @since 2.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $features = json_decode($request->get_param('value'));

        $formID = $this->settingsRepository->get('form_id');

        $this->handleFormFeatures($formID, $features);

        return [
            'data' => [
                'setting' => 'features',
                'value'   => $features,
                'formID'  => $formID,
            ],
        ];
    }

    /**
     * @since 3.15.0 Update the v3 form features based on Wizard settings.
     *
     * @param $formID
     * @param $features
     *
     * @return void
     * @throws Exception
     */
    public function handleFormFeatures($formID, $features): void
    {
        $donationForm = DonationForm::find($formID);

        if (!$donationForm) {
            return;
        }

        // Donation Goal
        $donationForm->settings->enableDonationGoal = in_array('donation-goal', $features, true);

        // Offline Donations
        $gateways = give_get_option('gateways_v3', []);
        if(in_array('offline-donations', $features, true)) {
            $gateways['offline'] = 1;
        } else {
            unset($gateways['offline']);
        }
        give_update_option('gateways_v3', $gateways);

        // Donation Comment
        $commentBlockExists = $donationForm->blocks->findByName('givewp/donor-comments');
        if (!in_array('donation-comments', $features, true) ) {
            $donationForm->blocks->remove('givewp/donor-comments');
        } elseif (!$commentBlockExists) {
            $donationForm->blocks->insertAfter('givewp/email', BlockFactory::donorComments());
        }

        // Terms and Conditions
        $termsBlockExists = $donationForm->blocks->findByName('givewp/terms-and-conditions');
        if (!in_array('terms-conditions', $features, true)) {
            $donationForm->blocks->remove('givewp/terms-and-conditions');
        } elseif (!$termsBlockExists) {
            $donationForm->blocks->insertBefore('givewp/payment-gateways', BlockFactory::termsAndConditions());
        }

        // Anonymous Donations
        $anonymousBlockExists = $donationForm->blocks->findByName('givewp/anonymous');
        if (!in_array('anonymous-donations', $features, true)) {
            $donationForm->blocks->remove('givewp/anonymous');
        } elseif (!$anonymousBlockExists) {
            $donationForm->blocks->insertAfter('givewp/email', BlockFactory::anonymousDonations());
        }

        // Company Donations
        $companyBlockExists = $donationForm->blocks->findByName('givewp/company');
        if (!in_array('company-donations', $features, true)) {
            $donationForm->blocks->remove('givewp/company');
        } elseif (!$companyBlockExists) {
            $donationForm->blocks->insertAfter('givewp/email', BlockFactory::company());
        }

        $donationForm->save();
    }
}
