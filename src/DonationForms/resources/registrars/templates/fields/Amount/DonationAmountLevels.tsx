import classNames from 'classnames';

/**
 * @since 3.0.0
 */
type DonationAmountLevelsProps = {
    name: string;
    currency: string;
    levels: {label: string; value: number}[];
    onLevelClick?: (amount: number) => void;
    descriptions: string[];
    descriptionsEnabled: boolean;
};

/**
 * @unreleased add level descriptions.
 * @since 3.0.0
 */
export default function DonationAmountLevels({
    name,
    currency,
    levels,
    onLevelClick,
}: DonationAmountLevelsProps) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;
    const amount = useWatch({name});
    const formatter = useCurrencyFormatter(currency);

    const levelsWithDescriptions = levels.filter((level) => level.label);
    const levelsWithoutDescriptions = levels.filter((level) => !level.label);

    const allLevels = [...levelsWithDescriptions, ...levelsWithoutDescriptions];

    return (
        <div className={'givewp-fields-amount__levels-container'}>
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
                                'givewp-fields-amount__level--description': !hasDescription,
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
