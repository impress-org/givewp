import {__} from '@wordpress/i18n';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import {getGoalDescription, getGoalFormattedValue} from '../utils';

import './styles.scss';

export default ({campaign}: { campaign: Campaign }) => {
    return (
        <div className="give-campaign-goal">
            <div className="give-campaign-goal__container">
                <div className="give-campaign-goal__container-item">
                    <span>{getGoalDescription(campaign.goalType)}</span>
                    <strong>
                        {getGoalFormattedValue(campaign.goalType, campaign.goalStats.actual)}
                    </strong>
                </div>
                <div className="give-campaign-goal__container-item">
                    <span>{__('Our goal', 'give')}</span>
                    <strong>
                        {getGoalFormattedValue(campaign.goalType, campaign.goal)}
                    </strong>
                </div>
            </div>
            <div className="give-campaign-goal__progress-bar">
                <div className="give-campaign-goal__progress-bar-container">
                    <div
                        className="give-campaign-goal__progress-bar-progress"
                        style={{width: `${campaign.goalStats.percentage}%`}}>
                    </div>
                </div>
            </div>
        </div>
    );
}
