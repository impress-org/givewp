<?php

namespace Give\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use Give\Framework\Receipts\DonationReceipt;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonationStatisticsController extends WP_REST_Controller
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = DonationRoute::NAMESPACE;
        $this->rest_base = DonationRoute::BASE;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<donationId>[\d]+)/statistics', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'donationId' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'mode' => [
                        'type' => 'string',
                        'default' => 'live',
                        'enum' => ['live', 'test'],
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @unreleased
     */
    public function get_item($request)
    {
        $donation = Donation::find($request->get_param('donationId'));
        if ( ! $donation) {
            return new WP_Error('donation_not_found', __('Donation not found', 'give'), ['status' => 404]);
        }

        $receipt = $donation->receipt();

        $item = [
            'donation' => [
                'amount' => $donation->amount->formatToDecimal(),
                'feeAmountRecovered' => $donation->feeAmountRecovered ? $donation->feeAmountRecovered->formatToDecimal() : 0,
                'status' => $donation->status->getValue(),
                'date' => $donation->createdAt->format('Y-m-d H:i:s'),
                'paymentMethod' => $donation->gatewayId,
            ],
            'donor' => [
                'id' => $donation->donorId,
                'name' => $donation->donor->name,
                'email' => $donation->donor->email,
            ],
            'campaign' => [
                'id' => $donation->campaignId,
                'title' => $donation->campaign->title,
            ],
            'receipt' => [
                'donationDetails' => $receipt->donationDetails->toArray(),
                'subscriptionDetails' => $receipt->subscriptionDetails->toArray(),
                'eventTicketsDetails' => $receipt->eventTicketsDetails->toArray(),
                'additionalDetails' => $receipt->additionalDetails->toArray(),
            ],
        ];

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return bool
     */
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @unreleased
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf('%s/%s/%d/%s', $this->namespace, $this->rest_base,
            $request->get_param('donationId'),
            'statistics'));

        $self_url = add_query_arg([
            'mode' => $request->get_param('mode'),
            'campaignId' => $request->get_param('campaignId'),
        ], $self_url);

        $links = [
            'self' => ['href' => $self_url],
        ];

        $response = new WP_REST_Response($item);
        $response->add_links($links);

        return $response;
    }
}
