import {useState} from '@wordpress/element';
import useCampaigns from '../../shared/hooks/useCampaigns';
import Pagination from '../../shared/components/Pagination';
import CampaignCard from '../../shared/components/CampaignCard';
import {CampaignGridType} from '../types';

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

export default ({attributes}: { attributes: CampaignGridType }) => {
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
                    <CampaignCard
                        key={campaign.id}
                        campaign={campaign}
                        showImage={attributes.showImage}
                        showDescription={attributes.showDescription}
                        showGoal={attributes.showGoal}
                    />
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
