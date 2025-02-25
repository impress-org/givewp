import {__} from '@wordpress/i18n';
import MergeCampaignsForm from './../Form';
import {MergeCampaignModalProps} from '@givewp/campaigns/admin/components/MergeCampaign/Form/types';
import {useState} from 'react';

/**
 * Create Campaign Modal component
 *
 * @unreleased
 */
export default function MergeCampaignModal({isOpen, setOpen, campaigns}: MergeCampaignModalProps) {
    const [isModalOpen, setIsModalOpen] = useState(isOpen);

    const closeModal = () => {
        setIsModalOpen(false);
        setOpen(false);
    };

    return (
        <>
            <MergeCampaignsForm
                isOpen={isModalOpen}
                handleClose={closeModal}
                title={__('Merge campaigns', 'give')}
                campaigns={campaigns}
            />
        </>
    );
}
