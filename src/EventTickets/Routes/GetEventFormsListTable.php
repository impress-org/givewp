<?php

namespace Give\EventTickets\Routes;

use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetEventFormsListTable
{
    /**
     * @var string
     */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)/forms/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function registerRoute(): void
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'event_id' => [
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($eventId) {
                            return Event::find($eventId);
                        },
                        'required' => true,
                    ],
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'default' => 'id',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'default' => 'asc',
                        'enum' => ['asc', 'desc'],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $forms = $this->getEventForms();
        $formsCount = $this->getEventFormsCount();
        $pageCount = (int)ceil($formsCount / $request->get_param('perPage'));

        $items = $this->prepareItems($forms);

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $formsCount,
                'totalPages' => $pageCount
            ]
        );
    }

    /**
     * @unreleased
     */
    public function getEventForms(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = ['id'];
        $sortDirection = $this->request->get_param('sortDirection') ?: 'asc';

        $query = DonationForm::query();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $forms = $query->getAll();

        if (!$forms) {
            return [];
        }

        return $forms;
    }

    /**
     * @unreleased
     */
    public function getEventFormsCount(): int
    {
        $query = DonationForm::query();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @unreleased
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $eventIdPattern = sprintf('"eventId":%s', $this->request->get_param('event_id'));

        $query->whereLike('give_formmeta_attach_meta_fields.meta_value', '%"name":"givewp/event-tickets"%')
            ->where(function ($query) use ($eventIdPattern) {
                $query->whereLike(
                    'give_formmeta_attach_meta_fields.meta_value',
                    "%$eventIdPattern}%"
                ) // When the eventId is the only block attribute.
                ->orWhereLike(
                    'give_formmeta_attach_meta_fields.meta_value',
                    "%$eventIdPattern,%"
                ); // When the eventId is the NOT only block attribute.
            });

        return $query;
    }


    /**
     * @unreleased
     *
     * @return bool|\WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('edit_posts')?: new \WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Events", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }

    /**
     * @unreleased
     */
    private function prepareItems(array $forms): array
    {
        return array_map(
            function (DonationForm $form) {
                return $this->formatColumns($form);
            },
            $forms
        );
    }

    private function formatColumns(DonationForm $form): array
    {
        $donationIds = give()->donations->getAllDonationIdsByFormId($form->id) ?? [];
        $soldTicketsCount = count($donationIds) > 0
            ? EventTicket::query()
                ->where('event_id', $this->request->get_param('event_id'))
                ->whereIn('donation_id', $donationIds)
                ->count()
            : 0;

        return [
            'id' => $form->id,
            'title' => "<a href='{get_edit_link($form->id)}'>{$form->title}</a>",
            'count' => $soldTicketsCount,
        ];
    }
}
