<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donation>
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
     * @since 4.8.0 Updated status to complete if subscription renewal
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $status = $model->status;

        if (isset($_REQUEST['subscriptionId']) && $model->type->isRenewal() && $model->status->isRenewal()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filter; it only changes the status label shown and saves nothing.
            $status = DonationStatus::COMPLETE();
        }

        return sprintf(
            '<div class="statusBadge statusBadge--%1$s"><p>%2$s</p></div>',
            $status->getValue(),
            $status->label()
        );
    }
}
