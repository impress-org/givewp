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

export default ({campaignId, handleSelect, children, inspectorControls = null, showInspectorControl = true}: CampaignSelectorProps) => {
    const [id] = useEntityProp('postType', 'page', 'campaignId');

    // set campaign id from context
    useEffect(() => {
        if (campaignId) {
            return;
        }

        if (id) {
            handleSelect(id);
        }
    }, []);

    const {campaigns, hasResolved} = useCampaigns({status: ['active', 'draft']});

    return (
        <>
            {!campaignId && (
                <Selector
                    handleSelect={(id: number) => handleSelect(id)}
                    campaigns={campaigns}
                    hasResolved={hasResolved}
                />
            )}

            {showInspectorControl && (
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
