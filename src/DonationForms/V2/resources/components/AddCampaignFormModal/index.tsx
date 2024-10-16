import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './AddCampaignFormModal.module.scss';
import {__} from '@wordpress/i18n';

/**
 * Form Modal component that renders a modal with a styled form inside
 *
 * @unreleased
 */
export default function AddCampaignFormModal({isOpen, handleClose, title, campaignId}: FormModalProps) {
    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={styles.formModal}
        >
            {/*<div className={`givewp-campaigns__form`}>
                <p>Options Goes Here...</p>
            </div>*/}

            <div>
                <>
                    <a
                        href={
                            'edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=new&campaignId=' +
                            campaignId
                        }
                        className={styles.addCampaignFormButton}
                    >
                        {__('Use Visual Form Builder', 'give')}
                    </a>
                    <br />
                    <br />
                    <a
                        href={'post-new.php?post_type=give_forms&campaignId=' + campaignId}
                        className={styles.addCampaignFormButton}
                    >
                        {__('Option-Based Form Editor', 'give')}
                    </a>
                </>
            </div>
        </ModalDialog>
    );
}

interface FormModalProps {
    isOpen: boolean;
    handleClose: () => void;
    title: string;
    campaignId: string;
}
