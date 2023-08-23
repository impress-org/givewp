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
}: GoalProps) {
    return (
        <div className="givewp-form-goal-progress">
            <label htmlFor="goal-progress" className="givewp-form-goal-progress-description">
                {__(`${currentAmountFormatted} of ${targetAmountFormatted} ${goalLabel}`, 'give')}
            </label>
            <progress
                id="goal-progress"
                className="givewp-form-goal-progress-meter"
                value={progressPercentage}
                max={100}
                aria-label={__(`${currentAmount} of ${targetAmount} ${goalLabel} goal`, 'give')}
            ></progress>
        </div>
    );
}
