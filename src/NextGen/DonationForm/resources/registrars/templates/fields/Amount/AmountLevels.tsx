import {useMemo} from 'react';
import classNames from 'classnames';

/**
 * @unreleased
 */
export default function AmountLevels({
                                         name,
                                         currency,
                                         levels,
                                         onLevelClick
                                     }: { name: string; currency: string; levels: Number[]; onLevelClick?: (amount: Number) => void }) {
    const {useWatch} = window.givewp.form.hooks;
    const amount = useWatch({name});
    const formatter = useMemo(
        () =>
            new Intl.NumberFormat(navigator.language, {
                style: 'currency',
                currency: currency,
            }),
        [currency, navigator.language]
    );

    return (
        <div className="givewp-fields-amount__levels--container">
            {levels.map((levelAmount, index) => {
                const label = formatter.format(Number(levelAmount));
                const selected = levelAmount === Number(amount);
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