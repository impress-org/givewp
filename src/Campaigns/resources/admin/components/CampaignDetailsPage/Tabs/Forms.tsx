import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';
import {useCampaignEntityRecord} from '@givewp/campaigns/utils';

/**
 * @since 4.0.0
 */
export default function CampaignDetailsFormsTab() {
    const entity = useCampaignEntityRecord();
    return <DonationFormsListTable entity={entity} />;
};
