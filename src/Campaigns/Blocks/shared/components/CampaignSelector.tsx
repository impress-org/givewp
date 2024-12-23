import useCampaignId from '../hooks/useCampaignId';
import CampaignDropdown from './CampaignDropdown';

export function CampaignSelector({attributes, setAttributes, children}) {
    const campaignId = useCampaignId(attributes, setAttributes);

    return (
        <>
            {!campaignId && !attributes?.campaignId && (
                <CampaignDropdown
                    campaignId={attributes?.campaignId}
                    setAttributes={setAttributes}
                    placement="inline"
                />
            )}

            {!campaignId && (
                <CampaignDropdown
                    campaignId={attributes?.campaignId}
                    setAttributes={setAttributes}
                    placement="sidebar"
                />
            )}

            {attributes?.campaignId && children}
        </>
    );
}
