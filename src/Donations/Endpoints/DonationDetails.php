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
     * @unreleased
     *
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
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'id' => [
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
                    ],
                    'totalDonation' => [
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
                    ],
                    'feeRecovered' => [
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
                    ],
                    'createdAt' => [
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
                    ],
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => array_values(DonationStatus::toArray()),
                    ],
                    'form' => [
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
                    ],
                ],
            ]
        );
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
            $totalDonation = $request->get_param('totalDonation');
            if ($totalDonation) {
                $donation->amount = Money::fromDecimal(
                    $totalDonation,
                    $donation->amount->getCurrency()
                );

                $updatedFields[] = 'totalDonation';
            }

            $feeRecovered = $request->get_param('feeRecovered');
            if ($feeRecovered) {
                $donation->feeAmountRecovered = Money::fromDecimal(
                    $feeRecovered,
                    $donation->amount->getCurrency()
                );

                $updatedFields[] = 'feeRecovered';
            }

            $createdAt = $request->get_param('createdAt');
            if ($createdAt) {
                $formattedDate = DateTime::createFromFormat(Temporal::ISO8601_JS, $createdAt);
                if ( ! $formattedDate) {
                    $formattedDate = Temporal::toDateTime($createdAt);
                }
                $donation->createdAt = $formattedDate;

                $updatedFields[] = 'createdAt';
            }

            $status = $request->get_param('status');
            if ($status) {
                $donation->status = new DonationStatus($status);

                $updatedFields[] = 'status';
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
}
