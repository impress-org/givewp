import {useState} from '@wordpress/element';
import useCampaigns from '../../shared/hooks/useCampaigns';
import {CampaignListType} from '../types';

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
        return (
            <div>loading</div>
        )
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
                            <div>

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
