<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class LevelsColumn extends ModelColumn
{

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'levels';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donation Levels', 'give');
    }

    /**
     * @since TBD Format with the shared money formatter; $locale is kept for add-on filters and no longer used.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        if (empty($model->levels)) {
            return __('No Levels', 'give');
        }

        return sprintf(
            '<div class="amount"><span>%s</span></div>',
            $this->getLevels($model->levels, $locale)
        );
    }

    /**
     * @since TBD Format with the shared money formatter; $locale is no longer used.
     * @since 2.24.0
     *
     * @param array $levels
     * @param string $locale
     *
     * @return string
     */
    private function getLevels(array $levels, string $locale): string
    {
        if (count($levels) === 1) {
            return MoneyFormatter::format($levels[0]->amount);
        }

        $levelsAmount = array_map(function ($level) {
            return $level->amount->formatToDecimal();
        }, $levels);

        $min = $levels[array_search(min($levelsAmount), $levelsAmount)];
        $max = $levels[array_search(max($levelsAmount), $levelsAmount)];

        if ($min === $max) {
            return MoneyFormatter::format($min->amount);
        }

        return sprintf(
            '%s - %s',
            MoneyFormatter::format($min->amount),
            MoneyFormatter::format($max->amount)
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::MONEY;
    }
}
