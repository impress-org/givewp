<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Subscription>
 */
class StatusColumn extends ModelColumn
{
    protected $sortColumn = 'status';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @unreleased
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

        if ('failed' === $model->status->getvalue()) {
            $extra = [
                'label' => __('failed', 'give'),
                'status' => 'failed',
                'text' => __('This subscription has failed', 'give'),
            ];
        } elseif (0 === $model->installments) {
            $extra = [
                'label' => __('indefinite', 'give'),
                'status' => 'indefinite',
                'text' => __('This subscription continues <strong>indefinitely</strong>', 'give'),
            ];
        } elseif (count($model->donations) < $model->installments) {
            $extra = [
                'label' => __('limited', 'give'),
                'status' => 'limited',
                'text' => sprintf(
                    __('This subscription has <strong>%d</strong> remaining donations', 'give'),
                    $model->installments - count($model->donations)
                )
            ];
        }

        return sprintf(
            $template,
            $model->status->getValue(),
            $model->status->label(),
            isset( $extra ) ? sprintf(
                $extraTemplate,
                GIVE_PLUGIN_URL . 'assets/dist/images/list-table/' . $extra['status'] . '-subscription-icon.svg',
                $extra['label'],
                $extra['status'],
                $extra['text']
            ) : ''
        );
    }
}
