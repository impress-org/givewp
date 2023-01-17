<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class StatusColumn extends ModelColumn
{
    protected $sortColumn = 'status';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $template = '
            <div class="subscriptionStatus">
                <div class="statusBadge statusBadge--%1$s"><div>%2$s</div></div>
                %3$s
            </div>
        ';

        $extraTemplate = '
            <div class="subscriptionStatus__container">
                <img src="%1$s" alt="%2$s"/>
                <div class="subscriptionStatus__message subscriptionStatus__message--%3$s">
                    <img src="%1$s" alt="%2$s"/>
                    <p>%4$s</p>
                </div>
            </div>
        ';

        if ($model->status->isFailing()) {
            $extra = [
                'label' => __('failed', 'give'),
                'status' => 'failed',
                'text' => __('This subscription has <strong>failed</strong>', 'give'),
            ];
        } elseif ($model->isIndefinite()) {
            $extra = [
                'label' => __('indefinite', 'give'),
                'status' => 'indefinite',
                'text' => __('This subscription continues <strong>indefinitely</strong>', 'give'),
            ];
        } elseif ($model->hasExceededTheMaxInstallments()) {
            $extra = [
                'label' => __('exceeded', 'give'),
                'status' => 'exceeded',
                'text' => __('This subscription has <strong>exceeded</strong> the expected donations. Try syncing with the gateway and cancelling if necessary.',
                    'give'),
            ];
        } elseif (0 < ($remainingInstallments = $model->remainingInstallments())) {
            $extra = [
                'label' => __('limited', 'give'),
                'status' => 'limited',
                'text' => sprintf(
                    _n(
                        'This subscription has <strong>%s</strong> remaining donation',
                        'This subscription has <strong>%s</strong> remaining donations',
                        $remainingInstallments,
                        'give'
                    ),
                    $remainingInstallments
                ),
            ];
        }

        return sprintf(
            $template,
            $model->status,
            $model->status->label(),
            isset($extra) ? sprintf(
                $extraTemplate,
                GIVE_PLUGIN_URL . 'assets/dist/images/list-table/' . $extra['status'] . '-subscription-icon.svg',
                $extra['label'],
                $extra['status'],
                $extra['text']
            ) : ''
        );
    }
}
