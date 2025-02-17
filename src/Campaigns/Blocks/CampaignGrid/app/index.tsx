import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import useCampaigns from '../../shared/hooks/useCampaigns';
import Pagination from '../../shared/components/Pagination';
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

    const {campaigns, hasResolved, totalPages} = useCampaigns({
        ids: attributes?.filterBy?.map((item: { value: string }) => Number(item.value)),
        per_page: attributes?.perPage,
        sortBy: attributes?.sortBy,
        orderBy: attributes?.orderBy,
        page,
    });

    if (!hasResolved) {
        return null;
    }

    return (
        <>
            <div
                className="givewp-campaign-grid"
                style={{gridTemplateColumns: `repeat(${getGridSettings(attributes.layout)}, 1fr)`}}
            >
                {campaigns?.map((campaign) => (
                    <div
                        className="givewp-campaign-grid__item"
                    >
                        {attributes.showImage && campaign.image && (
                            <div
                                style={{backgroundImage: `url(${campaign.image})`}}
                                className="givewp-campaign-grid__item-image">
                            </div>
                        )}
                        <div className="givewp-campaign-grid__item-title">
                            {campaign.title}
                        </div>
                        {attributes.showDescription && (
                            <div className="givewp-campaign-grid__item-description">
                                {campaign.shortDescription}
                            </div>
                        )}

                        {attributes.showGoal && (
                            <div className="givewp-campaign-grid__item__goal">
                                <div className="givewp-campaign-grid__item__goal-progress">
                                    <div
                                        className="givewp-campaign-grid__item__goal-progress-container">
                                        <div
                                            className="givewp-campaign-grid__item__goal-progress-bar"
                                            style={{width: `${campaign.goalStats.percentage}%`}}>
                                        </div>
                                    </div>
                                </div>
                                <div className="ive-campaigns-campaignListBlock-grid-item__goal-container">
                                    <div className="givewp-campaign-grid__item__goal-container-item">
                                        <span>{getGoalDescription(campaign.goalType)}</span>
                                        <strong>
                                            {getGoalFormattedValue(campaign.goalType, campaign.goalStats.actual)}
                                        </strong>
                                    </div>
                                    <div className="givewp-campaign-grid__item__goal-container-item">
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

            {attributes.showPagination && totalPages >= page && (
                <div className="givewp-campaign-grid__pagination">
                    <Pagination currentPage={page} totalPages={totalPages} setPage={(number) => setPage(number)} />
                </div>
            )}
        </>
    )
}
