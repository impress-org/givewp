import {useEffect} from 'react';
import Inspector from './Inspector';
import useCampaigns from '../../hooks/useCampaigns';
import Selector from './Selector';
import {useEntityProp} from '@wordpress/core-data';

type CampaignSelectorProps = {
    campaignId: number;
    children: JSX.Element | JSX.Element[],
    handleSelect: (id: number) => void;
    inspectorControls?: JSX.Element | JSX.Element[];
    showInspectorControl?: boolean;
}

export default function CampaignSelector({campaignId, handleSelect, children, inspectorControls = null, showInspectorControl = true}: CampaignSelectorProps){
    const [id] = useEntityProp('postType', 'page', 'campaignId');
    const {campaigns, hasResolved} = useCampaigns({status: ['active', 'draft']});

    // set campaign id from context
    useEffect(() => {
        // if campaign page ID changes, update the campaign ID in block attributes
        // or default the campaignId in the block attributes to the campaign page ID
        if (id && campaignId !== id) {
            handleSelect(id);
        }
    }, [id]);

    return (
        <>
            {!campaignId && (
                <Selector
                    handleSelect={(id: number) => handleSelect(id)}
                    campaigns={campaigns}
                    hasResolved={hasResolved}
                />
            )}

            {!id && showInspectorControl && (
                <Inspector
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
