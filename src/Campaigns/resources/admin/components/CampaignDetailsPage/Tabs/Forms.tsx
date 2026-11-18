import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';
import {useCampaignEntityRecord} from '@givewp/campaigns/utils';
import listTableScreen from './listTableScreen';

/**
 * @since TBD Pass the Forms tab's own screen config.
 * @since 4.0.0
 */
export default function CampaignDetailsFormsTab() {
    const entity = useCampaignEntityRecord();
    return <DonationFormsListTable entity={entity} screen={listTableScreen} />;
};
