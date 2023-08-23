import {__} from '@wordpress/i18n';
import {GoalProps} from '@givewp/forms/propTypes';

const GoalTemplate = window.givewp.form.templates.layouts.goal;

/**
 * @since 3.0.0
 */
const FormStats = ({totalRevenue, goalTargetAmount, totalCountValue, totalCountLabel}) => {
    return (
        <aside className="givewp-form-stats-panel">
            <ul className="givewp-form-stats-panel-list">
                <li className="givewp-form-stats-panel-stat">
                    <span className="givewp-form-stats-panel-stat-number">{totalRevenue} </span> {__('raised', 'give')}
                </li>
                <li className="givewp-form-stats-panel-stat">
                    <span className="givewp-form-stats-panel-stat-number">{totalCountValue} </span> {totalCountLabel}
                </li>
                <li className="givewp-form-stats-panel-stat">
                    <span className="givewp-form-stats-panel-stat-number">{goalTargetAmount} </span>{' '}
                    {__('goal', 'give')}
                </li>
            </ul>
        </aside>
    );
};

/**
 * @since 3.0.0
 */
export default function Goal(props: GoalProps) {
    const {targetAmountFormatted, totalRevenueFormatted, totalCountValue, totalCountLabel} = props;

    return (
        <div style={{width: '100%'}}>
            <FormStats
                totalRevenue={totalRevenueFormatted}
                goalTargetAmount={targetAmountFormatted}
                totalCountValue={totalCountValue}
                totalCountLabel={totalCountLabel}
            />

            <GoalTemplate {...props} />
        </div>
    );
}
