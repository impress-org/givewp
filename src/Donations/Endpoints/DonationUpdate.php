<?php

namespace Give\Donations\Endpoints;

use Give\Donations\Endpoints\DonationUpdateAttributes\AmountAttribute;
use Give\Donations\Endpoints\DonationUpdateAttributes\AttributeUpdatesModel;
use Give\Donations\Endpoints\DonationUpdateAttributes\CreatedAtAttribute;
use Give\Donations\Endpoints\DonationUpdateAttributes\FeeAmountRecoveredAttribute;
use Give\Donations\Endpoints\DonationUpdateAttributes\FormIdAttribute;
use Give\Donations\Endpoints\DonationUpdateAttributes\IdAttribute;
use Give\Donations\Endpoints\DonationUpdateAttributes\StatusAttribute;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/*
 * Class DonationDetails
 *
 * @unreleased
 */

class DonationUpdate extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donation/(?P<id>[\d]+)';

    /**
     * @var array
     */
    private $attributes = [
        IdAttribute::class,
        StatusAttribute::class,
        AmountAttribute::class,
        FeeAmountRecoveredAttribute::class,
        FormIdAttribute::class,
        CreatedAtAttribute::class,
    ];

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
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => $this->getRouteAttributes(),
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
            foreach ($this->attributes as $attr) {
                $attrId = $attr::getId();
                $value = $request->get_param($attrId);

                if (is_null($value) || ! is_a($attr, AttributeUpdatesModel::class, true)) {
                    continue;
                }

                $actualValue = $donation->{$attrId};
                $attr::update($value, $donation);
                $updatedValue = $donation->{$attrId};

                if ($actualValue !== $updatedValue) {
                    $updatedFields[] = $attrId;
                }
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
        $routeAttributes = [];

        foreach ($this->attributes as $attribute) {
            $routeAttributes[$attribute::getId()] = $attribute::getDefinition();
        }

        return $routeAttributes;
    }
}
