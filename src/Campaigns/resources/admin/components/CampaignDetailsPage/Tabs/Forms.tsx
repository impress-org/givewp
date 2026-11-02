import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';
import {useCampaignEntityRecord} from '@givewp/campaigns/utils';
import listTableScreen from './listTableScreen';

/**
 * @since TBD Pass the campaign as a prop. It fixes the list's campaign.
 * @since TBD Pass the Forms tab's own screen config.
 * @since 4.0.0
 */
export default function CampaignDetailsFormsTab() {
    const entity = useCampaignEntityRecord();
    const campaignId = entity.record?.id;

    // Without the campaign the list would show every form, so wait until the record has loaded.
    if (!campaignId) {
        return null;
    }

    return <DonationFormsListTable entity={entity} campaignId={campaignId} screen={listTableScreen} />;
};
