import {WarningIcon} from '@givewp/campaigns/admin/components/Icons';
import {__} from '@wordpress/i18n';

export default function DraftCampaignPageNotice() {
    return (
        <>
            <WarningIcon />
            <span>
                {__(
                    "Your campaign page won't be visible until it's published. Use the 'Edit Campaign Page' button to open the page editor and publish it.",
                    'give'
                )}
            </span>
        </>
    );
}
