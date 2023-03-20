<?php

namespace Give\Donations\Endpoints;

use DateTime;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/*
 * @unreleased
 */
class DonationDetails extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donation/(?P<id>[\d]+)';

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function registerRoute()
    {
        $this->registerIdAttribute();
        $this->registerTotalDonationAttribute();
        $this->registerFeeRecoveredAttribute();
        $this->registerCreatedAtAttribute();
        $this->registerStatusAttribute();
        $this->registerFormAttribute();

        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => $this->getRouteAttributes(),
            ]
        );
    }

    /**
     * Register id attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerIdAttribute()
    {
        $this->attributes['id'] = [
            'type' => 'integer',
            'required' => true,
            'validate_callback' => function ($param) {
                if (give()->donations->getById($param) === null) {
                    return new WP_Error(
                        'donation_not_found',
                        __('Donation not found.', 'give'),
                        ['status' => 404]
                    );
                }

                return true;
            },
        ];
    }

    /**
     * Register totalDonation attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerTotalDonationAttribute()
    {
        $this->attributes['totalDonation'] = [
            'type' => 'number',
            'required' => false,
            'validate_callback' => function ($param) {
                if ($param < 0) {
                    return new WP_Error(
                        'invalid_total_donation',
                        __('Invalid total donation.', 'give'),
                        ['status' => 400]
                    );
                }

                return true;
            },
            'update_callback' => function ($value, $donation) {
                $donation->amount = Money::fromDecimal(
                    $value,
                    $donation->amount->getCurrency()
                );

                return $donation;
            },
        ];
    }

    /**
     * Register feeRecovered attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerFeeRecoveredAttribute()
    {
        $this->attributes['feeRecovered'] = [
            'type' => 'number',
            'required' => false,
            'validate_callback' => function ($param) {
                if ($param < 0) {
                    return new WP_Error(
                        'invalid_fee_recovered',
                        __('Invalid fee recovered.', 'give'),
                        ['status' => 400]
                    );
                }

                return true;
            },
            'update_callback' => function ($value, $donation) {
                $donation->feeAmountRecovered = Money::fromDecimal(
                    $value,
                    $donation->feeAmountRecovered->getCurrency()
                );

                return $donation;
            },
        ];
    }

    /**
     * Register createdAt attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerCreatedAtAttribute()
    {
        $this->attributes['createdAt'] = [
            'type' => 'string',
            'required' => false,
            'validate_callback' => function ($param) {
                if ( ! DateTime::createFromFormat(Temporal::ISO8601_JS,
                        $param) && ! Temporal::toDateTime($param)) {
                    return new WP_Error(
                        'invalid_date',
                        __('Invalid date.', 'give'),
                        ['status' => 400]
                    );
                }

                return true;
            },
            'update_callback' => function ($value, $donation) {
                $date = DateTime::createFromFormat(Temporal::ISO8601_JS, $value);
                if ( ! $date) {
                    $date = Temporal::toDateTime($value);
                }
                $donation->createdAt = $date;

                return $donation;
            },
        ];
    }

    /**
     * Register status attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerStatusAttribute()
    {
        $this->attributes['status'] = [
            'type' => 'string',
            'required' => false,
            'enum' => array_values(DonationStatus::toArray()),
            'update_callback' => function ($value, $donation) {
                $donation->status = $value;

                return $donation;
            },
        ];
    }

    /**
     * Register form attribute.
     *
     * @unreleased
     *
     * @return void
     */
    private function registerFormAttribute()
    {
        $this->attributes['form'] = [
            'type' => 'integer',
            'required' => false,
            'validate_callback' => function ($param) {
                if (give()->donationForms->getById($param) === null) {
                    return new WP_Error(
                        'form_not_found',
                        __('Form not found.', 'give'),
                        ['status' => 404]
                    );
                }

                return true;
            },
            'update_callback' => function ($value, $donation) {
                $form = give()->donationForms->getById($value);
                $donation->formId = $value;
                $donation->formTitle = $form->title;

                return $donation;
            },
        ];
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_give_payments')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Donations', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $donation = give()->donations->getById($id);

        $updatedFields = [];

        try {
            foreach ($this->attributes as $attr => $attrArgs) {
                if ( ! $request->has_param($attr) || ! array_key_exists('update_callback', $attrArgs)) {
                    continue;
                }

                $value = $request->get_param($attr);
                $donation = $attrArgs['update_callback']($value, $donation);
                $updatedFields[] = $attr;
            }

            $formId = $request->get_param('form');
            if ($formId) {
                $form = give()->donationForms->getById($formId);
                $donation->formId = $formId;
                $donation->formTitle = $form->title;

                $updatedFields[] = 'form';
            }

            $donation->save();
        } catch (Exception $e) {
            return new WP_Error(
                'donation_update_failed',
                __('Donation update failed.', 'give'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'updatedFields' => $updatedFields,
            ]
        );
    }

    /**
     * @return array
     */
    private function getRouteAttributes(): array
    {
        return array_map(
            function ($attribute) {
                if (array_key_exists('update_callback', $attribute)) {
                    unset($attribute['update_callback']);
                }

                return $attribute;
            },
            $this->attributes
        );
    }
}
