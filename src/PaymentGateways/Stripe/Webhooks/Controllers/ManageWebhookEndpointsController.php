<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Controllers;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Actions\CheckWebhookEndpointHealth;
use Give\PaymentGateways\Stripe\Webhooks\Actions\CreateWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Actions\DeleteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Actions\EnsureWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Actions\GetStripeAccountsForMode;
use Give\PaymentGateways\Stripe\Webhooks\Actions\ListWebhookEndpoints;
use Give\PaymentGateways\Stripe\Webhooks\Actions\UpdateWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\WebhookEndpointsModalView;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;

/**
 * Backs the Webhook Status field and its modal on the Stripe Manage Accounts page.
 *
 * Operations: "status" (read-only, every account), "list" (one account), and "create", "update" and "delete" on one
 * endpoint of one account. The last three answer with the refreshed modal content.
 *
 * @since TBD
 */
final class ManageWebhookEndpointsController
{
    public const ACTION = 'givewp_stripe_manage_webhook_endpoints';

    private const OPERATIONS = ['status', 'list', 'create', 'update', 'delete'];

    /**
     * Health state shown when Stripe could not be read.
     */
    private const STATE_ERROR = 'error';

    /**
     * @var GetStripeAccountsForMode
     */
    private $getAccounts;

    /**
     * @var ListWebhookEndpoints
     */
    private $listEndpoints;

    /**
     * @var CreateWebhookEndpoint
     */
    private $createEndpoint;

    /**
     * @var UpdateWebhookEndpoint
     */
    private $updateEndpoint;

    /**
     * @var DeleteWebhookEndpoint
     */
    private $deleteEndpoint;

    /**
     * @var CheckWebhookEndpointHealth
     */
    private $checkHealth;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @var WebhookEndpointsModalView
     */
    private $view;

    /**
     * @since TBD
     */
    public function __construct(
        GetStripeAccountsForMode $getAccounts,
        ListWebhookEndpoints $listEndpoints,
        CreateWebhookEndpoint $createEndpoint,
        UpdateWebhookEndpoint $updateEndpoint,
        DeleteWebhookEndpoint $deleteEndpoint,
        CheckWebhookEndpointHealth $checkHealth,
        WebhookTarget $target,
        WebhookEndpointsModalView $view
    ) {
        $this->getAccounts = $getAccounts;
        $this->listEndpoints = $listEndpoints;
        $this->createEndpoint = $createEndpoint;
        $this->updateEndpoint = $updateEndpoint;
        $this->deleteEndpoint = $deleteEndpoint;
        $this->checkHealth = $checkHealth;
        $this->target = $target;
        $this->view = $view;
    }

    /**
     * @since TBD
     */
    public function __invoke(): void
    {
        check_ajax_referer(self::ACTION);

        if (!current_user_can('manage_give_settings')) {
            wp_send_json_error(null, 403);
        }

        $operation = $this->input('operation');

        if (!in_array($operation, self::OPERATIONS, true)) {
            wp_send_json_error(null, 400);
        }

        $mode = give_is_test_mode() ? 'test' : 'live';
        $accounts = ($this->getAccounts)($mode);

        if ('status' === $operation) {
            wp_send_json_success(['states' => $this->states($accounts, $mode)]);
        }

        $account = $accounts[$this->input('account')] ?? null;

        if (!$account) {
            wp_send_json_error(null, 404);
        }

        $notice = 'list' === $operation ? [] : $this->change($operation, $account, $mode);

        try {
            $endpoints = ($this->listEndpoints)($account, $mode);
        } catch (WebhookEndpointException $exception) {
            $this->logFailure('list', $account, $mode, $exception);

            wp_send_json_success([
                'html' => $this->view->render([], $mode, $this->errorNotice($exception)),
                'state' => self::STATE_ERROR,
            ]);
        }

        wp_send_json_success([
            'html' => $this->view->render($endpoints, $mode, $notice),
            'state' => $this->checkHealth->fromEndpoints($endpoints)->getValue(),
        ]);
    }

    /**
     * The health state of every account that holds a key for the mode. A read that fails reports "error" for that
     * account only.
     *
     * @since TBD
     *
     * @param array<string, AccountDetail> $accounts
     *
     * @return array<string, string> state keyed by account slug
     */
    private function states(array $accounts, string $mode): array
    {
        $states = [];

        foreach ($accounts as $slug => $account) {
            try {
                $states[$slug] = ($this->checkHealth)($account, $mode)->getValue();
            } catch (WebhookEndpointException $exception) {
                $states[$slug] = self::STATE_ERROR;

                WebhookLogger::debug(
                    'Stripe webhook status could not be read.',
                    array_merge(
                        ['account' => $slug, 'account_type' => $account->type, 'mode' => $mode],
                        WebhookLogger::failureContext($exception)
                    )
                );
            }
        }

        return $states;
    }

    /**
     * Applies a create, update or delete and returns the message to show above the refreshed list.
     *
     * @since TBD
     *
     * @return array{level: string, message: string}
     */
    private function change(string $operation, AccountDetail $account, string $mode): array
    {
        try {
            switch ($operation) {
                case 'create':
                    return $this->create($account, $mode);
                case 'update':
                    return $this->update($account, $mode);
                default:
                    return $this->delete($account, $mode);
            }
        } catch (WebhookEndpointException $exception) {
            $this->logFailure($operation, $account, $mode, $exception);

            return $this->errorNotice($exception);
        }
    }

    /**
     * @since TBD
     *
     * @return array{level: string, message: string}
     *
     * @throws WebhookEndpointException
     */
    private function create(AccountDetail $account, string $mode): array
    {
        $url = $this->url();
        $events = $this->events();

        if (!$url || !$events) {
            return $this->notice('error', __('Enter a URL and choose at least one event.', 'give'));
        }

        if (count(($this->listEndpoints)($account, $mode)) >= WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT) {
            return $this->notice(
                'error',
                __('This account has the maximum of 16 webhook endpoints. Delete one to add another.', 'give')
            );
        }

        $created = ($this->createEndpoint)(
            $account,
            $mode,
            $url,
            $events,
            EnsureWebhookEndpoint::API_VERSION,
            'GiveWP'
        );

        $this->logChange('created', $account, $mode, $created->id);

        return $this->notice('success', __('Webhook endpoint added.', 'give'));
    }

    /**
     * @since TBD
     *
     * @return array{level: string, message: string}
     *
     * @throws WebhookEndpointException
     */
    private function update(AccountDetail $account, string $mode): array
    {
        $endpointId = $this->input('endpoint_id');
        $url = $this->url();
        $existing = $this->find(($this->listEndpoints)($account, $mode), $endpointId);

        if (!$existing || !$url) {
            return $this->notice('error', __('The webhook endpoint was not found.', 'give'));
        }

        /* The form shows the required events only, so every other event on the endpoint stays as it is. */
        $kept = array_diff($existing->enabledEvents, WebhookTarget::REQUIRED_EVENTS);
        $events = in_array('*', $existing->enabledEvents, true) ? $existing->enabledEvents : array_merge(
            $kept,
            $this->events()
        );

        if (!$events) {
            return $this->notice('error', __('Choose at least one event.', 'give'));
        }

        ($this->updateEndpoint)($account, $mode, $endpointId, [
            'url' => $url,
            'disabled' => 'disabled' === $this->input('status'),
            'enabled_events' => array_values($events),
        ]);

        $this->logChange('updated', $account, $mode, $endpointId);

        return $this->notice('success', __('Webhook endpoint saved.', 'give'));
    }

    /**
     * @since TBD
     *
     * @return array{level: string, message: string}
     *
     * @throws WebhookEndpointException
     */
    private function delete(AccountDetail $account, string $mode): array
    {
        $endpointId = $this->input('endpoint_id');

        ($this->deleteEndpoint)($account, $mode, $endpointId);

        $this->logChange('deleted', $account, $mode, $endpointId);

        return $this->notice('success', __('Webhook endpoint deleted.', 'give'));
    }

    /**
     * @since TBD
     *
     * @param WebhookEndpointDto[] $endpoints
     */
    private function find(array $endpoints, string $endpointId): ?WebhookEndpointDto
    {
        foreach ($endpoints as $endpoint) {
            if ($endpoint->id === $endpointId) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * The required events that were checked in the form.
     *
     * @since TBD
     *
     * @return string[]
     */
    private function events(): array
    {
        $posted = isset($_POST['events']) ? array_map('sanitize_text_field', (array)wp_unslash($_POST['events'])) : [];

        return array_values(array_intersect(WebhookTarget::REQUIRED_EVENTS, $posted));
    }

    /**
     * @since TBD
     */
    private function url(): string
    {
        return esc_url_raw($this->input('url'));
    }

    /**
     * @since TBD
     */
    private function input(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
    }

    /**
     * @since TBD
     *
     * @return array{level: string, message: string}
     */
    private function notice(string $level, string $message): array
    {
        return ['level' => $level, 'message' => $message];
    }

    /**
     * @since TBD
     *
     * @return array{level: string, message: string}
     */
    private function errorNotice(WebhookEndpointException $exception): array
    {
        return $this->notice(
            'error',
            sprintf(
                /* translators: %s: Error message from Stripe. */
                __('Stripe returned an error: %s', 'give'),
                $exception->getMessage()
            )
        );
    }

    /**
     * @since TBD
     */
    private function logChange(string $change, AccountDetail $account, string $mode, string $endpointId): void
    {
        WebhookLogger::info(
            sprintf('Stripe webhook endpoint %s from the Manage Accounts modal.', $change),
            [
                'account' => $account->accountSlug,
                'account_type' => $account->type,
                'mode' => $mode,
                'endpoint_id' => $endpointId,
            ]
        );
    }

    /**
     * @since TBD
     */
    private function logFailure(
        string $operation,
        AccountDetail $account,
        string $mode,
        WebhookEndpointException $exception
    ): void {
        WebhookLogger::warning(
            'Stripe webhook request from the Manage Accounts modal failed.',
            array_merge(
                [
                    'operation' => $operation,
                    'account' => $account->accountSlug,
                    'account_type' => $account->type,
                    'mode' => $mode,
                ],
                WebhookLogger::failureContext($exception)
            )
        );
    }
}
