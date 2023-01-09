<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorType;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donor>
 */
class DonorTypeColumn extends ModelColumn
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donorType';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Type', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        $donorType = give()->donors->getDonorType($model->id);

        $template = '
            <div class="badge">
                <img role="img" aria-labelledby="badgeId-%1$d" class="icon icon--%2$s" src="%3$s" alt="%4$s" />
                <p id="badgeId-%1$d" class="badge__label">%5$s</p>
            </div>
        ';

        $icons = [
            DonorType::NEW => 'new-donor-icon.svg',
            DonorType::SUBSCRIBER => 'recurring-donation-icon.svg',
            DonorType::REPEAT => 'repeat-donor-icon.svg',
            DonorType::SINGLE => 'onetime-donation-icon.svg',
        ];

        return sprintf(
            $template,
            $model->id,
            $donorType->getValue(),
            GIVE_PLUGIN_URL . 'assets/dist/images/list-table/' . $icons[$donorType->getValue()],
            sprintf( __('%s icon', 'give'), $donorType->label()),
            $donorType->label()
        );
    }
}
