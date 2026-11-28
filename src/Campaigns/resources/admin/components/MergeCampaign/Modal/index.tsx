import {__} from '@wordpress/i18n';
import MergeCampaignsForm from './../Form';
import {MergeCampaignModalProps} from '@givewp/campaigns/admin/components/MergeCampaign/Form/types';

/**
 * Create Campaign Modal component
 *
 * @since TBD Pass `onMerged` to the form, so the list reloads after a merge.
 * @since 4.0.0
 */
export default function MergeCampaignModal({isOpen, setOpen, campaigns, onMerged}: MergeCampaignModalProps) {
    const closeModal = () => {
        setOpen(false);
    };

    return (
        <>
            <MergeCampaignsForm
                isOpen={isOpen}
                handleClose={closeModal}
                onMerged={onMerged}
                title={__('Merge campaigns', 'give')}
                campaigns={campaigns}
            />
        </>
    );
}
