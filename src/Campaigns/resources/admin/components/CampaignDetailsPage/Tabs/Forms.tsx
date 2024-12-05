import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';
import {useCampaignEntityRecord} from '@givewp/campaigns/utils';

/**
 * @unreleased
 */
export default () => {
    const entity = useCampaignEntityRecord();
    return <DonationFormsListTable entity={entity} />;
};
