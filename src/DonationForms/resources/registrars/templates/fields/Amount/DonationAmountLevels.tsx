import classNames from 'classnames';

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

type Level = {label: string | null; value: number};

/**
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

    return (
        <div
            className={classNames('givewp-fields-amount__levels-container', {
                'givewp-fields-amount__levels-container--has-descriptions': groupedLevels.labeled.length > 0,
            })}
        >
            {allLevels.map((level, index) => {
                const label = formatter.format(level.value);
                const selected = level.value === amount;
                const hasDescription = level.label;

                return (
                    <div
                        className={classNames('givewp-fields-amount__level-container', {
                            'givewp-fields-amount__level-container--col': hasDescription,
                        })}
                    >
                        <button
                            className={classNames('givewp-fields-amount__level', {
                                'givewp-fields-amount__level--selected': selected,
                                'givewp-fields-amount__level--description': hasDescription,
                            })}
                            type="button"
                            onClick={() => {
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
