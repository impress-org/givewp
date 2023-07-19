import classNames from 'classnames';

/**
 * @since 0.2.0
 */
type DonationAmountLevelsProps = {
    name: string;
    currency: string;
    levels: number[];
    onLevelClick?: (amount: number) => void;
};

/**
 * @unreleased rename to DonationAmountLevels
 * @since 0.2.0
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

    return (
        <div className="givewp-fields-amount__levels-container">
            {levels.map((levelAmount, index) => {
                const label = formatter.format(levelAmount);
                const selected = levelAmount === amount;
                return (
                    <button
                        className={classNames('givewp-fields-amount__level', {
                            'givewp-fields-amount__level--selected': selected,
                        })}
                        type="button"
                        onClick={() => {
                            onLevelClick(levelAmount);
                        }}
                        key={index}
                    >
                        {label}
                    </button>
                );
            })}
        </div>
    );
}