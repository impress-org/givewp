<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Webhooks\Actions\EnsureWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;

/**
 * The HTML inside the modal that lists the webhook endpoints of one Stripe account, with the edit, delete and
 * create forms. Every value is escaped here, so the JavaScript only inserts the returned markup.
 *
 * @since TBD
 */
final class WebhookEndpointsModalView
{
    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(WebhookTarget $target)
    {
        $this->target = $target;
    }

    /**
     * @since TBD
     *
     * @param WebhookEndpointDto[]                 $endpoints
     * @param array{level: string, message: string} $notice   Result of the last action; empty for none.
     */
    public function render(array $endpoints, string $mode, array $notice = []): string
    {
        ob_start();
        ?>
        <div class="give-stripe-webhooks">
            <?php echo $this->notice($notice); ?>

            <p class="give-stripe-webhooks__summary">
                <?php
                echo esc_html(sprintf(
                    /* translators: 1: Number of webhook endpoints. 2: Maximum number of webhook endpoints. 3: "live" or "test". */
                    __('%1$d of %2$d endpoints on this account (%3$s mode).', 'give'),
                    count($endpoints),
                    WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT,
                    $mode
                ));
                ?>
            </p>

            <?php if (!$endpoints) : ?>
                <p class="give-stripe-webhooks__empty">
                    <?php esc_html_e('This account has no webhook endpoints.', 'give'); ?>
                </p>
            <?php endif; ?>

            <ul class="give-stripe-webhooks__list">
                <?php foreach ($endpoints as $endpoint) : ?>
                    <?php echo $this->endpoint($endpoint); ?>
                <?php endforeach; ?>
            </ul>

            <?php echo $this->createForm(count($endpoints)); ?>
        </div>
        <?php

        return (string)ob_get_clean();
    }

    /**
     * @since TBD
     *
     * @param array{level: string, message: string} $notice
     */
    private function notice(array $notice): string
    {
        if (!$notice) {
            return '';
        }

        return sprintf(
            '<div class="give-stripe-webhooks__notice give-stripe-webhooks__notice--%s" role="status">%s</div>',
            esc_attr($notice['level']),
            esc_html($notice['message'])
        );
    }

    /**
     * @since TBD
     */
    private function endpoint(WebhookEndpointDto $endpoint): string
    {
        $isSite = $this->target->matches($endpoint->url);
        $missing = $this->target->missingEvents($endpoint);
        $enabled = 'enabled' === $endpoint->status;

        ob_start();
        ?>
        <li class="give-stripe-webhooks__item<?php echo $isSite ? ' give-stripe-webhooks__item--site' : ''; ?>">
            <div class="give-stripe-webhooks__head">
                <span class="give-stripe-webhooks__url"><?php echo esc_html($endpoint->url); ?></span>
                <?php if ($isSite) : ?>
                    <span class="give-stripe-webhooks__badge give-stripe-webhooks__badge--site">
                        <?php esc_html_e('This site', 'give'); ?>
                    </span>
                <?php endif; ?>
                <span class="give-stripe-webhooks__badge give-stripe-webhooks__badge--<?php echo esc_attr($endpoint->status); ?>">
                    <?php echo esc_html($enabled ? __('Enabled', 'give') : __('Disabled', 'give')); ?>
                </span>
            </div>

            <dl class="give-stripe-webhooks__details">
                <dt><?php esc_html_e('Events', 'give'); ?></dt>
                <dd>
                    <?php
                    if (in_array('*', $endpoint->enabledEvents, true)) {
                        esc_html_e('All events', 'give');
                    } else {
                        echo esc_html(sprintf(
                            /* translators: 1: Number of required events the endpoint sends. 2: Number of required events. */
                            __('%1$d of %2$d required', 'give'),
                            count(WebhookTarget::REQUIRED_EVENTS) - count($missing),
                            count(WebhookTarget::REQUIRED_EVENTS)
                        ));
                    }
                    ?>
                    <?php if ($missing && $isSite) : ?>
                        <span class="give-stripe-webhooks__missing">
                            <?php echo esc_html(sprintf(
                                /* translators: %s: Comma separated list of Stripe event names. */
                                __('Missing: %s', 'give'),
                                implode(', ', $missing)
                            )); ?>
                        </span>
                    <?php endif; ?>
                </dd>
                <dt><?php esc_html_e('Mode', 'give'); ?></dt>
                <dd><?php echo esc_html($endpoint->livemode ? __('Live', 'give') : __('Test', 'give')); ?></dd>
                <dt><?php esc_html_e('Created', 'give'); ?></dt>
                <dd><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $endpoint->created)); ?></dd>
            </dl>

            <details class="give-stripe-webhooks__edit">
                <summary><?php esc_html_e('Edit', 'give'); ?></summary>
                <form class="give-stripe-webhooks__form" data-webhook-operation="update">
                    <input type="hidden" name="endpoint_id" value="<?php echo esc_attr($endpoint->id); ?>">
                    <label>
                        <span><?php esc_html_e('URL', 'give'); ?></span>
                        <input type="url" name="url" value="<?php echo esc_attr($endpoint->url); ?>" required>
                    </label>
                    <label>
                        <span><?php esc_html_e('Status', 'give'); ?></span>
                        <select name="status">
                            <option value="enabled"<?php selected($enabled); ?>><?php esc_html_e('Enabled', 'give'); ?></option>
                            <option value="disabled"<?php selected(!$enabled); ?>><?php esc_html_e('Disabled', 'give'); ?></option>
                        </select>
                    </label>
                    <?php echo $this->eventFields($endpoint->enabledEvents); ?>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save changes', 'give'); ?>
                    </button>
                </form>
            </details>

            <form class="give-stripe-webhooks__delete" data-webhook-operation="delete"
                  data-confirm="<?php echo esc_attr(__('Delete this webhook endpoint from Stripe? This cannot be undone.', 'give')); ?>">
                <input type="hidden" name="endpoint_id" value="<?php echo esc_attr($endpoint->id); ?>">
                <button type="submit" class="button-link button-link-delete">
                    <?php esc_html_e('Delete', 'give'); ?>
                </button>
            </form>
        </li>
        <?php

        return (string)ob_get_clean();
    }

    /**
     * @since TBD
     */
    private function createForm(int $endpointCount): string
    {
        if ($endpointCount >= WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT) {
            return sprintf(
                '<p class="give-stripe-webhooks__notice give-stripe-webhooks__notice--warning">%s</p>',
                esc_html__(
                    'This account has the maximum of 16 webhook endpoints. Delete one to add another.',
                    'give'
                )
            );
        }

        ob_start();
        ?>
        <details class="give-stripe-webhooks__create">
            <summary class="button"><?php esc_html_e('Add endpoint', 'give'); ?></summary>
            <form class="give-stripe-webhooks__form" data-webhook-operation="create">
                <label>
                    <span><?php esc_html_e('URL', 'give'); ?></span>
                    <input type="url" name="url" value="<?php echo esc_attr($this->target->url()); ?>" required>
                </label>
                <?php echo $this->eventFields(WebhookTarget::REQUIRED_EVENTS); ?>
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Add endpoint', 'give'); ?>
                </button>
            </form>
        </details>
        <?php

        return (string)ob_get_clean();
    }

    /**
     * One checkbox per required event. Events outside that list are not shown and are kept as they are when an
     * endpoint is saved. An endpoint that sends every event ("*") shows them all checked and cannot be narrowed here.
     *
     * @since TBD
     *
     * @param string[] $selected
     */
    private function eventFields(array $selected): string
    {
        $all = in_array('*', $selected, true);

        ob_start();
        ?>
        <fieldset class="give-stripe-webhooks__events"<?php disabled($all); ?>>
            <legend><?php esc_html_e('Events', 'give'); ?></legend>
            <?php foreach (WebhookTarget::REQUIRED_EVENTS as $event) : ?>
                <label>
                    <input type="checkbox" name="events[]" value="<?php echo esc_attr($event); ?>"<?php checked($all || in_array($event, $selected, true)); ?>>
                    <code><?php echo esc_html($event); ?></code>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php

        return (string)ob_get_clean();
    }
}
