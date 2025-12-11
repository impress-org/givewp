import {__} from '@wordpress/i18n';
import MergeCampaignsForm from './../Form';
import {MergeCampaignModalProps} from '@givewp/campaigns/admin/components/MergeCampaign/Form/types';

/**
 * Create Campaign Modal component
 *
 * @since 4.0.0
 */
export default function MergeCampaignModal({isOpen, setOpen, campaigns}: MergeCampaignModalProps) {
    const closeModal = () => {
        setOpen(false);
    };

    return (
        <>
            <MergeCampaignsForm
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Merge campaigns', 'give')}
                campaigns={campaigns}
            />
        </>
    );
}
