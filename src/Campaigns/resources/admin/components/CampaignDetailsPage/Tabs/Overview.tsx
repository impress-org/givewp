import {__} from '@wordpress/i18n';
import CampaignStats from '../Components/CampaignStats';
import CampaignNotice from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/Notices/CampaignNotice';
import {useCampaignNoticeHook} from '@givewp/campaigns/hooks';

/**
 * @unreleased
 */
export default function CampaignDetailsOverviewTab() {
    const [showTooltip, dismissTooltip] = useCampaignNoticeHook('givewp_campaign_form_notice');

    return (
        <div>
            <div>
                <CampaignStats />
            </div>
            {showTooltip && (
                <CampaignNotice
                    title={__('Campaign Form', 'give')}
                    description={__('Get a quick view of all the forms associated with your campaign in the forms page. You can edit and add multiple forms to your campaign.', 'give')}
                    linkHref="#"
                    linkText={__('All you need to know about campaigns', 'give')}
                    handleDismiss={dismissTooltip}
                    type={'campaignForm'}
                />
            )}
        </div>
    );
};
