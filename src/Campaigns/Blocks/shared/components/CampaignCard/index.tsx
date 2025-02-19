import {__} from '@wordpress/i18n';
import {getGoalDescription, getGoalFormattedValue} from '../../../CampaignGoal/utils';
import {Campaign} from '@givewp/campaigns/admin/components/types';

import './styles.scss';

export default ({showImage, showGoal, showDescription, campaign}: {
    showImage: boolean,
    showDescription: boolean,
    showGoal: boolean,
    campaign: Campaign
}) => {

    return (
        <div
            className="give-campaigns-component-campaign"
            {...(campaign.pagePermalink && {
                style: {
                    cursor: 'pointer',
                },
                onClick: () => window.location = campaign.pagePermalink
            })}
        >
            {showImage && campaign.image && (
                <div
                    style={{backgroundImage: `url(${campaign.image})`}}
                    className="give-campaigns-component-campaign-image">
                </div>
            )}
            <div className="give-campaigns-component-campaign-title">
                {campaign.title}
            </div>
            {showDescription && (
                <div className="give-campaigns-component-campaign-description">
                    {campaign.shortDescription}
                </div>
            )}

            {showGoal && (
                <div className="give-campaigns-component-campaign__goal">
                    <div className="give-campaigns-component-campaign__goal-progress">
                        <div
                            className="give-campaigns-component-campaign__goal-progress-container">
                            <div
                                className="give-campaigns-component-campaign__goal-progress-bar"
                                style={{width: `${campaign.goalStats.percentage}%`}}>
                            </div>
                        </div>
                    </div>
                    <div className="give-campaigns-component-campaign__goal-container">
                        <div className="give-campaigns-component-campaign__goal-container-item">
                            <span>{getGoalDescription(campaign.goalType)}</span>
                            <strong>
                                {getGoalFormattedValue(campaign.goalType, campaign.goalStats.actual)}
                            </strong>
                        </div>
                        <div className="give-campaigns-component-campaign__goal-container-item">
                            <span>{__('Our goal', 'give')}</span>
                            <strong>
                                {getGoalFormattedValue(campaign.goalType, campaign.goal)}
                            </strong>
                        </div>
                    </div>
                </div>
            )}
        </div>
    )
}
