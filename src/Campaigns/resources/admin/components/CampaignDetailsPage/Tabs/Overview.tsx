import {useState} from 'react';
import CampaignStats from '../Components/CampaignStats';
import {getCampaignOptionsWindowData, handleTooltipDismiss} from '@givewp/campaigns/utils';
import CampaignFormNotice from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/Notices/CampaignForm';

/**
 * @unreleased
 */
export default () => {
    const campaignWindowData = getCampaignOptionsWindowData();
    const [showTooltip, setShowTooltip] = useState(campaignWindowData.admin.showCampaignFormNotice);
    const dismissTooltip = () => handleTooltipDismiss('givewp_campaign_form_notice').then(() => setShowTooltip(false))

    return (
        <div>
            <div>
                <CampaignStats />
            </div>
            {showTooltip && <CampaignFormNotice handleClick={dismissTooltip} />}
        </div>
    );
};
