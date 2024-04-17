import classNames from 'classnames';

/**
 * @since 3.0.0
 */
type DonationAmountLevelsProps = {
    name: string;
    currency: string;
    levels: number[];
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
    descriptions,
    descriptionsEnabled,
}: DonationAmountLevelsProps) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;
    const amount = useWatch({name});
    const formatter = useCurrencyFormatter(currency);

    return (
        <div className={'givewp-fields-amount__levels-container'}>
            {levels.map((levelAmount, index) => {
                const label = formatter.format(levelAmount);
                const selected = levelAmount === amount;
                const hasDescription = descriptionsEnabled && descriptions[index] !== '';

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
                                onLevelClick(levelAmount);
                            }}
                            key={index}
                        >
                            {label}
                        </button>
                        {hasDescription && (
                            <span className={'givewp-fields-amount__level__description'}>{descriptions[index]}</span>
                        )}
                    </div>
                );
            })}
        </div>
    );
}
