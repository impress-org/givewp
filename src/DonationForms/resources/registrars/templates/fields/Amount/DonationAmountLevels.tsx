import classNames from 'classnames';
import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';

/**
 * @since 3.0.0
 */
type DonationAmountLevelsProps = {
    name: string;
    currency: string;
    levels: Level[];
    onLevelClick?: (amount: number) => void;
};

type GroupedLevels = {
    labeled: Level[];
    unlabeled: Level[];
};

type Level = {label: string | null; value: number; checked?: boolean};

/**
 * Prefer the level marked as checked in the form builder when amounts are duplicated.
 *
 * @unreleased
 */
function getSelectedLevelIndex(levels: Level[], amount: unknown): number {
    const checkedIndex = levels.findIndex((level) => level.checked);

    if (checkedIndex >= 0 && levels[checkedIndex].value === amount) {
        return checkedIndex;
    }

    const matchingIndices = levels.reduce<number[]>((indices, level, index) => {
        if (level.value === amount) {
            indices.push(index);
        }

        return indices;
    }, []);

    return matchingIndices.length === 1 ? matchingIndices[0] : -1;
}

/**
 * @unreleased Track the selected level by index to support duplicate amounts.
 * @since 4.3.0 Add proper roles and ARIA attributes
 * @since 3.12.0 add level descriptions.
 * @since 3.0.0
 */
export default function DonationAmountLevels({name, currency, levels, onLevelClick}: DonationAmountLevelsProps) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;
    const amount = useWatch({name});
    const formatter = useCurrencyFormatter(currency);

    const groupedLevels: GroupedLevels = levels.reduce(
        (acc: GroupedLevels, level) => {
            const key = level.label ? 'labeled' : 'unlabeled';
            acc[key].push(level);
            return acc;
        },
        {labeled: [], unlabeled: []}
    );

    const allLevels = [...groupedLevels.labeled, ...groupedLevels.unlabeled];

    const [selectedIndex, setSelectedIndex] = useState<number>(() => getSelectedLevelIndex(allLevels, amount));

    useEffect(() => {
        if (allLevels[selectedIndex]?.value !== amount) {
            setSelectedIndex(getSelectedLevelIndex(allLevels, amount));
        }
    }, [levels, amount]);

    return (
        <div
            className={classNames('givewp-fields-amount__levels-container', {
                'givewp-fields-amount__levels-container--has-descriptions': groupedLevels.labeled.length > 0,
            })}
            role="radiogroup"
            aria-label={__('Donation Amount', 'give')}
        >
            {allLevels.map((level, index) => {
                const label = formatter.format(level.value);
                const selected = index === selectedIndex;
                const hasDescription = level.label;

                return (
                    <div
                        className={classNames('givewp-fields-amount__level-container', {
                            'givewp-fields-amount__level-container--col': hasDescription,
                        })}
                        key={index}
                    >
                        <button
                            className={classNames('givewp-fields-amount__level', {
                                'givewp-fields-amount__level--selected': selected,
                                'givewp-fields-amount__level--description': hasDescription,
                            })}
                            type="button"
                            role="radio"
                            aria-checked={selected}
                            onClick={() => {
                                setSelectedIndex(index);
                                onLevelClick(level.value);
                            }}
                            key={index}
                        >
                            {label}
                        </button>
                        {hasDescription && (
                            <span className={'givewp-fields-amount__level__description'}>{level.label}</span>
                        )}
                    </div>
                );
            })}
        </div>
    );
}
