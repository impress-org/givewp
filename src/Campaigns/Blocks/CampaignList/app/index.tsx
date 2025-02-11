import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import useCampaigns from '../../shared/hooks/useCampaigns';
import {CampaignListType} from '../types';
import {getGoalDescription, getGoalFormattedValue} from '../../CampaignGoal/utils';

import './styles.scss';

const getGridSettings = (layout: string) => {
    switch (layout) {
        case 'double':
            return 2;
        case 'triple':
            return 3;
        default:
            return 1;
    }
}

export default ({attributes}: { attributes: CampaignListType }) => {
    const [page, setPage] = useState(1);

    const {campaigns, hasResolved} = useCampaigns({
        ids: attributes?.filterBy?.map((item: { value: string }) => Number(item.value)),
        per_page: attributes?.perPage,
        page,
    });


    if (!hasResolved) {
        return null;
    }

    return (
        <>
            <div
                className="give-campaigns-campaignListBlock-grid"
                style={{gridTemplateColumns: `repeat(${getGridSettings(attributes.layout)}, 1fr)`}}
            >
                {campaigns?.map((campaign) => (
                    <div
                        className="give-campaigns-campaignListBlock-grid-item"
                    >
                        {attributes.showImage && campaign.image && (
                            <div
                                style={{backgroundImage: `url(${campaign.image})`}}
                                className="give-campaigns-campaignListBlock-grid-item-image">
                            </div>
                        )}
                        <div className="give-campaigns-campaignListBlock-grid-item-title">
                            {campaign.title}
                        </div>
                        {attributes.showDescription && (
                            <div className="give-campaigns-campaignListBlock-grid-item-description">
                                {campaign.shortDescription}
                            </div>
                        )}

                        {attributes.showGoal && (
                            <div className="give-campaigns-campaignListBlock-grid-item__goal">
                                <div className="give-campaigns-campaignListBlock-grid-item__goal-progress">
                                    <div
                                        className="give-campaigns-campaignListBlock-grid-item__goal-progress-container">
                                        <div
                                            className="give-campaigns-campaignListBlock-grid-item__goal-progress-bar"
                                            style={{width: `${campaign.goalStats.percentage}%`}}>
                                        </div>
                                    </div>
                                </div>
                                <div className="ive-campaigns-campaignListBlock-grid-item__goal-container">
                                    <div className="give-campaigns-campaignListBlock-grid-item__goal-container-item">
                                        <span>{getGoalDescription(campaign.goalType)}</span>
                                        <strong>
                                            {getGoalFormattedValue(campaign.goalType, campaign.goalStats.actual)}
                                        </strong>
                                    </div>
                                    <div className="give-campaigns-campaignListBlock-grid-item__goal-container-item">
                                        <span>{__('Our goal', 'give')}</span>
                                        <strong>
                                            {getGoalFormattedValue(campaign.goalType, campaign.goal)}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {campaigns.length > 0 && attributes.showPagination && (
                <div>
                    Pagination
                </div>
            )}
        </>
    )
}
