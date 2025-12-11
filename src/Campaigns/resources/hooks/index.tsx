import {useState} from 'react';
import {getCampaignOptionsWindowData, handleTooltipDismiss} from '@givewp/campaigns/utils';

type NoticeId = 'givewp_campaign_form_notice' | 'givewp_campaign_settings_notice' | 'givewp_campaign_listtable_notice';

const noticesMap = {
    givewp_campaign_listtable_notice: 'showCampaignListTableNotice',
    givewp_campaign_settings_notice: 'showCampaignSettingsNotice',
    givewp_campaign_form_notice: 'showCampaignFormNotice',
};

export function useCampaignNoticeHook(id: NoticeId): [boolean, () => void] {
    const campaignWindowData = getCampaignOptionsWindowData();
    const [showTooltip, setShowTooltip] = useState<boolean>(campaignWindowData.admin[noticesMap[id]]);

    return [
        showTooltip,
        () => {
            setShowTooltip(false);
            campaignWindowData.admin[noticesMap[id]] = false;
            handleTooltipDismiss(id);
        },
    ];
}
