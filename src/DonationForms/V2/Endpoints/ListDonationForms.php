<?php

namespace Give\DonationForms\V2\Endpoints;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\WhereQueryBuilder;
use Give\Helpers\Language;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.19.0
 */
class ListDonationForms extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms';

    /**
     * @var WP_REST_Request
     */
    private $request;

    /**
     * @var DonationFormsListTable
     */
    protected $listTable;

    /**
     * @var int
     */
    protected $defaultForm;

    /**
     * @since TBD Accept a list of statuses and of campaigns, and a created date range
     * @since 4.0.0 Add campaignId parameter
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 30,
                        'minimum' => 1,
                    ],
                    'status' => [
                        'type' => 'array',
                        'required' => false,
                        'default' => ['any'],
                        'maxItems' => 50,
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'publish',
                                'future',
                                'draft',
                                'pending',
                                'trash',
                                'auto-draft',
                                'inherit',
                                'any',
                                'upgraded',
                            ],
                        ],
                        'description' => 'Filter forms by one or more statuses. At most 50. "any" means every status except trash.',
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => [
                            'asc',
                            'desc',
                        ],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                    'return' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'columns',
                        'enum' => [
                            'model',
                            'columns',
                        ],
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'required' => false,
                    ],
                    'campaign' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateCampaignListSize'],
                        'description' => 'A campaign id, a comma-separated list of up to 50 campaign ids, "none" for standalone forms, or empty for all forms.',
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'format' => 'date-time',
                        'description' => 'Only forms created on or after this date and time (site time zone).',
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'format' => 'date-time',
                        'description' => 'Only forms created on or before this date and time (site time zone).',
                    ],
                ],
            ]
        );
    }

    /**
     * @since 4.19.0 Resolve isDefaultCampaignForm from the row's campaign when the request names none; expose campaignType and campaignTitle
     * @since 3.22.0 Add locale support
     * @since 2.24.0 Change this to use the new ListTable class
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(DonationFormsListTable::class);
        $campaignId = (int)($this->request->get_param('campaignId'));
        $campaign = $campaignId ? Campaign::find($campaignId) : null;
        $defaultCampaignForm = $campaign ? $campaign->defaultForm() : null;

        $this->defaultForm = $defaultCampaignForm->id ?? 0;

        $forms = $this->getForms();
        $totalForms = $this->getTotalFormsCount();
        $totalPages = (int)ceil($totalForms / $this->request->get_param('perPage'));

        $formsData = DonationFormDataRepository::forms($forms);

        $this->listTable->setData($formsData);

        // get p2p forms
        $p2pForms = DB::table('give_campaigns')
            ->select('form_id')
            ->where('campaign_type', CampaignType::CORE, '!=')
            ->getAll(ARRAY_A);

        if ('model' === $this->request->get_param('return')) {
            $items = array_map(function ($form) {
                return $form->toArray();
            }, $forms);
        } else {
            $this->listTable->items($forms, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();

            foreach ($items as $i => &$item) {
                $queryArgs = [
                    'locale' => Language::getLocale(),
                ];

                foreach ($p2pForms as $form) {
                    if ($item['id'] == $form['form_id']) {
                        $queryArgs['p2p'] = true;
                        break;
                    }
                }

                $campaign = $formsData->getCampaign($forms[$i]);

                $item['name'] = $forms[$i]->title;
                $item['edit'] = add_query_arg($queryArgs, get_edit_post_link($item['id'], 'edit'));
                $item['permalink'] = get_permalink($item['id']);
                $item['v3form'] = $forms[$i]->usesFormBuilder;
                $item['status_raw'] = $forms[$i]->status->getValue();
                /* $defaultCampaignForm only exists when the request names a campaign, so the global list falls back to the row's campaign. */
                $item['isDefaultCampaignForm'] = $defaultCampaignForm
                    ? $item['id'] === $defaultCampaignForm->id
                    : ($campaign && $campaign->defaultFormId === (int)$item['id']);
                $item['campaignId'] = $campaign->id ?? 0;
                $item['campaignType'] = $campaign->type ?? '';
                $item['campaignTitle'] = $campaign->title ?? '';
            }
        }

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $totalForms,
                'totalPages' => $totalPages,
                'trash' => defined('EMPTY_TRASH_DAYS') && EMPTY_TRASH_DAYS > 0,
                'defaultForm' => $this->defaultForm,
            ]
        );
    }

    /**
     * @since 3.2.0 added distinct() to the query
     * @since 2.24.0 Refactor to query through the ModelQueryBuilder
     *
     * @return array
     */
    public function getForms(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');

        $query = give()->donationForms->prepareQuery();

        $query = $this->getWhereConditions($query);

        $query->orderByRaw('FIELD(ID, %d) DESC', $this->defaultForm);

        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';
        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donationForms = $query->distinct()->getAll();

        if ( ! $donationForms) {
            return [];
        }

        return $donationForms;
    }

    /**
     * @since 2.24.0 Refactor to query through the ModelQueryBuilder
     *
     * @return int
     */
    public function getTotalFormsCount(): int
    {
        $query = DB::table('posts')
            ->where('post_type', 'give_forms');

        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * Rejects an array and a campaign list of more than 50 items. Any other value is accepted, as it always was.
     *
     * @since TBD
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validateCampaignListSize($value): bool
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }

        if (! is_string($value)) {
            return true;
        }

        return count(explode(',', $value)) <= 50;
    }

    /**
     * @since TBD Filter by several statuses and campaigns, and by created date range
     * @since 4.19.0 Add "campaign" filter: a campaign id or "none" for standalone forms; campaign id filter includes the campaign's default form
     * @since      4.0.0 Add "campaignId" support
     * @since      2.24.0
     *
     * @param QueryBuilder $query
     *
     * @return QueryBuilder
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');
        $statuses = array_values(array_filter((array)$this->request->get_param('status'), 'is_string'));
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');

        // Status: a list with "any" is today's default set
        if (! $statuses || in_array('any', $statuses, true)) {
            $query->whereIn('post_status', ['publish', 'draft', 'pending', 'private', 'upgraded']);
        } else {
            $query->whereIn('post_status', $statuses);
        }

        // Created date
        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } elseif ($start) {
            $query->where('post_date', $start, '>=');
        } elseif ($end) {
            $query->where('post_date', $end, '<=');
        }

        // Search
        if ($search) {
            if (ctype_digit($search)) {
                $query->where('ID', $search);
            } else {
                $searchTerms = array_map('trim', explode(' ', $search));
                foreach ($searchTerms as $term) {
                    if ($term) {
                        $query->whereLike('post_title', $term);
                    }
                }
            }
        }

        $campaign = (string)$this->request->get_param('campaign');
        $campaignIds = $this->request->get_param('campaignId')
            ? [(int)$this->request->get_param('campaignId')]
            : array_values(array_filter(array_map('intval', array_filter(explode(',', $campaign), 'ctype_digit'))));

        // Forms attached through the campaign forms table, or a campaign's own default form
        // (how Peer-to-Peer campaigns link their form)
        if ($campaignIds) {
            $query->where(function (WhereQueryBuilder $builder) use ($campaignIds) {
                $builder
                    ->whereIn('ID', function (QueryBuilder $subQuery) use ($campaignIds) {
                        $subQuery
                            ->from('give_campaign_forms')
                            ->select('form_id')
                            ->whereIn('campaign_id', $campaignIds);
                    })
                    ->orWhereIn('ID', function (QueryBuilder $subQuery) use ($campaignIds) {
                        $subQuery
                            ->from('give_campaigns')
                            ->select('form_id')
                            ->whereIn('id', $campaignIds);
                    });
            });
        }

        // Standalone forms: no campaign forms row and not a Peer-to-Peer campaign's form
        if ($campaign === 'none') {
            $query
                ->whereNotIn('ID', function (QueryBuilder $builder) {
                    $builder
                        ->from('give_campaign_forms')
                        ->select('form_id');
                })
                ->whereNotIn('ID', function (QueryBuilder $builder) {
                    $builder
                        ->from('give_campaigns')
                        ->select('form_id')
                        ->where('campaign_type', CampaignType::CORE, '!=');
                });
        }

        return $query;
    }
}
