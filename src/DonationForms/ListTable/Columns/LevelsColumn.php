<?php

declare(strict_types=1);

namespace Give\DonationForms\ListTable\Columns;

use Give\DonationForms\Models\DonationForm;
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
            return $levels[0]->amount->formatToLocale($locale);
        }

        $levelsAmount = array_map(function ($level) use ($locale) {
            return $level->amount->formatToDecimal();
        }, $levels);

        $min = $levels[array_search(min($levelsAmount), $levelsAmount)];
        $max = $levels[array_search(max($levelsAmount), $levelsAmount)];

        if ($min === $max) {
            return $min->amount->formatToLocale($locale);
        }

        return sprintf(
            '%s - %s',
            $min->amount->formatToLocale($locale),
            $max->amount->formatToLocale($locale)
        );
    }
}
