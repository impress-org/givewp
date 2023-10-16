import {__} from '@wordpress/i18n';
import type {GoalProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Goal({
    currentAmount,
    currentAmountFormatted,
    targetAmount,
    targetAmountFormatted,
    goalLabel,
    progressPercentage,
    totalCountLabel,
    totalCountValue,
    totalRevenue,
    totalRevenueFormatted,
}: GoalProps) {
    return (
        <>
            <aside className="givewp-layouts-goal__stats-panel">
                <ul className="givewp-layouts-goal__stats-panel__list">
                    <Stat value={totalRevenueFormatted} label={__('Raised', 'give')} />
                    <Stat value={totalCountValue} label={totalCountLabel} />
                    <Stat value={targetAmountFormatted} label={__('Goal', 'give')} />
                </ul>
            </aside>
            <div className="givewp-layouts-goal__progress">
                <label htmlFor="goal-progress" className="givewp-layouts-goal__progress__description">
                    {__(`${currentAmountFormatted} of ${targetAmountFormatted} ${goalLabel}`, 'give')}
                </label>
                <progress
                    id="goal-progress"
                    className="givewp-layouts-goal__progress__meter"
                    value={progressPercentage}
                    max={100}
                    aria-label={__(`${currentAmount} of ${targetAmount} ${goalLabel} goal`, 'give')}
                ></progress>
                <div className="givewp-layouts-goal__progress__markers">
                    <span className="givewp-layouts-goal__progress__marker">
                        {currentAmountFormatted} {goalLabel}
                    </span>
                    <span className="givewp-layouts-goal__progress__marker">
                        {targetAmountFormatted} {goalLabel}
                    </span>
                </div>
            </div>
        </>
    );
}

function Stat({value, label}: {value: string | number; label: string}) {
    return (
        <li className="givewp-layouts-goal__stats-panel__list-item">
            <span className="givewp-layouts-goal__stats-panel__stat-value">{value} </span>{' '}
            <span className="givewp-layouts-goal__stats-panel__stat-label">{label}</span>
        </li>
    );
}
