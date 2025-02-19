import {useEffect} from 'react';
import {select} from '@wordpress/data';
import CampaignDropdown from './CampaignDropdown';
import useCampaigns from '../hooks/useCampaigns';
import CampaignSelector from './CampaignSelector/index';

type CampaignSelectorProps = {
    campaignId: number;
    children: JSX.Element | JSX.Element[],
    handleSelect: (id: number) => void;
    inspectorControls?: JSX.Element | JSX.Element[];
    showInspectorControl?: boolean;
}

export default ({campaignId, handleSelect, children, inspectorControls = null, showInspectorControl = false}: CampaignSelectorProps) => {

    // set campaign id from context
    useEffect(() => {
        if (campaignId) {
            return;
        }
        // @ts-ignore
        const id = select('core/editor').getEditedPostAttribute('campaignId');

        if (id) {
            handleSelect(id);
        }
    }, []);

    const {campaigns, hasResolved} = useCampaigns();

    return (
        <>
            {!campaignId && (
                <CampaignSelector
                    handleSelect={(id: number) => handleSelect(id)}
                    campaigns={campaigns}
                    hasResolved={hasResolved}
                />
            )}

            {showInspectorControl && (
                <CampaignDropdown
                    campaignId={campaignId}
                    campaigns={campaigns}
                    hasResolved={hasResolved}
                    handleSelect={(id: number) => handleSelect(id)}
                    inspectorControls={inspectorControls}
                />
            )}

            {campaignId && children}
        </>
    );
}
