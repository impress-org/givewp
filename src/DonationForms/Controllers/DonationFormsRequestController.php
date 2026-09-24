<?php

namespace Give\DonationForms\Controllers;

use Exception;
use Give\Campaigns\Actions\CacheCampaignData;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Routes\Permissions\DonationFormPermissions;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\DonationFormsRoute;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.2.0
 */
class DonationFormsRequestController
{
    /**
     * @since 4.10.1 Added status check to ensure non-authorized users can only access published forms
     * @since 4.2.0
     */
    public function getForm(WP_REST_Request $request)
    {
        $form = DonationForm::find($request->get_param('id'));

        if ( ! $form) {
            return new WP_REST_Response(__('Form not found', 'give'), 404);
        }

        if (!$form->status->isPublished() && !DonationFormPermissions::canViewPrivate()) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to view this donation form.', 'give'),
                ['status' => DonationFormPermissions::authorizationStatusCode()]
            );
        }

        return new WP_REST_Response($form->toArray());
    }

    /**
     * @since 4.2.0
     */
    public function getForms(WP_REST_Request $request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $status = $request->get_param('status');

        $query = DonationForm::query();

        if ( ! in_array('orphaned', $status)) {
            $query->whereIn('post_status', $status);
        } else {
            // get orphaned forms only
            $query
                ->whereNotIn('ID', function (QueryBuilder $builder) {
                    $builder
                        ->from('give_campaign_forms')
                        ->select('form_id');
                })
                // p2p forms
                ->whereNotIn('ID', function (QueryBuilder $builder) {
                    $builder
                        ->from('give_campaigns')
                        ->select('form_id')
                        ->where('campaign_type', CampaignType::CORE, '!=');
                });
        }

        if ( ! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $totalQuery = clone $query;

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $forms = $query->getAll() ?? [];
        $totalForms = empty($forms) ? 0 : $totalQuery->count();
        $totalPages = $totalForms === 0 ? 0 : (int)ceil($totalForms / $perPage);

        $forms = array_map(function ($form) {
            return $form->toArray();
        }, $forms);

        $response = rest_ensure_response($forms);
        $response->header('X-WP-Total', $totalForms);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(DonationFormsRoute::FORMS)
        );

        if ($page > 1) {
            $prevPage = $page - 1;

            if ($prevPage > $totalPages) {
                $prevPage = $totalPages;
            }

            $response->link_header('prev', add_query_arg('page', $prevPage, $base));
        }

        if ($totalPages > $page) {
            $nextPage = $page + 1;
            $response->link_header('next', add_query_arg('page', $nextPage, $base));
        }

        return $response;
    }

    /**
     * Links forms to a campaign. A form that already belongs to another campaign is moved: past
     * donations stay with the original campaign and both campaigns' cached totals are refreshed.
     * A campaign's default form cannot be moved, and Peer-to-Peer forms stay with their campaign.
     *
     * @since TBD Move forms that already belong to a campaign, refuse default and Peer-to-Peer forms, refresh both campaigns' cached totals.
     * @since 4.2.0
     *
     * @throws Exception
     */
    public function associateFormsWithCampaign(WP_REST_Request $request): WP_REST_Response
    {
        $formIDs = array_map('intval', (array)$request->get_param('formIDs'));
        $campaignId = $request->get_param('campaignId');
        $campaignRepository = give(CampaignRepository::class);
        $campaign = $campaignRepository->getById($campaignId);

        if ( ! $campaign) {
            return new WP_REST_Response('Campaign not found', 404);
        }

        if ( ! $formIDs) {
            return new WP_REST_Response([]);
        }

        $peerToPeerForms = DB::table('give_campaigns')
            ->select('form_id')
            ->where('campaign_type', CampaignType::CORE, '!=')
            ->whereIn('form_id', $formIDs)
            ->getAll();

        if ($peerToPeerForms) {
            return new WP_REST_Response([
                'code' => 'givewp_peer_to_peer_form',
                'message' => __('Peer-to-Peer forms cannot be moved to another campaign.', 'give'),
            ], 400);
        }

        /* Refuse the whole request before moving anything, so a batch never half-applies. */
        try {
            foreach ($formIDs as $formID) {
                $currentCampaign = $campaignRepository->getByFormId($formID);

                if ($currentCampaign && $currentCampaign->id !== $campaign->id) {
                    $campaignRepository->validateFormIsNotDefault($currentCampaign, $formID);
                }
            }
        } catch (InvalidArgumentException $exception) {
            return new WP_REST_Response([
                'code' => 'givewp_default_campaign_form',
                'message' => $exception->getMessage(),
            ], 400);
        }

        $campaignIdsToRefresh = [$campaign->id];

        try {
            foreach ($formIDs as $formID) {
                $previousCampaign = $campaignRepository->moveCampaignForm($campaign, $formID);

                if ($previousCampaign) {
                    $campaignIdsToRefresh[] = $previousCampaign->id;
                }
            }
        } finally {
            foreach (array_unique($campaignIdsToRefresh) as $id) {
                give(CacheCampaignData::class)->dispatch($id);
            }
        }

        return new WP_REST_Response($formIDs);
    }
}
