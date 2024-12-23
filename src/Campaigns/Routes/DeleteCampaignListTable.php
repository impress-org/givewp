<?php

namespace Give\Campaigns\Routes;

use Give\API\RestRoute;
use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class DeleteCampaignListTable implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint = 'campaigns/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var CampaignsListTable
     */
    protected $listTable;

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
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => function ($ids) {
                            foreach ($this->splitString($ids) as $id) {
                                if ( ! filter_var($id, FILTER_VALIDATE_INT)) {
                                    return false;
                                }
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
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_Rest_Response
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = [];
        $successes = [];

        foreach ($ids as $id) {
            $campaignDeleted = give(CampaignRepository::class)->getById($id)->delete();
            $campaignDeleted ? $successes[] = $id : $errors[] = $id;
        }

        return new WP_REST_Response(['errors' => $errors, 'successes' => $successes]);
    }


    /**
     * Split string
     *
     * @unreleased
     *
     * @return string[]
     */
    protected function splitString(string $ids): array
    {
        if (strpos($ids, ',')) {
            return array_map('trim', explode(',', $ids));
        }

        return [trim($ids)];
    }

    /**
     * @unreleased
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('delete_posts') ?: new WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to delete Campaigns", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
