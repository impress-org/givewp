import {__} from '@wordpress/i18n';
import {Notice} from '@wordpress/components';
import {Spinner} from '@givewp/components';
import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';
import {useCampaignEntityRecord} from '@givewp/campaigns/utils';
import listTableScreen from './listTableScreen';

/**
 * @since TBD Pass the campaign as a prop. It fixes the list's campaign.
 * @since TBD Pass the Forms tab's own screen config.
 * @since TBD Show a spinner while the campaign loads and a notice when it cannot be loaded.
 * @since 4.0.0
 */
export default function CampaignDetailsFormsTab() {
    const entity = useCampaignEntityRecord();
    const campaignId = entity.record?.id;

    // Without the campaign the list would show every form, so wait until the record has loaded.
    if (!campaignId) {
        if (entity.hasResolved) {
            return (
                <Notice status="error" isDismissible={false}>
                    {__('The campaign could not be loaded, so its forms cannot be listed.', 'give')}
                </Notice>
            );
        }

        return <Spinner />;
    }

    // The list reads the campaign once when it mounts, so a new campaign needs a new list.
    return <DonationFormsListTable key={campaignId} entity={entity} campaignId={campaignId} screen={listTableScreen} />;
}
