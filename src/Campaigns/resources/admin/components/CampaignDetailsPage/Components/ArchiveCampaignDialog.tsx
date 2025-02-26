import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon} from '../../Icons';
import styles from '../CampaignDetailsPage.module.scss'

/**
 * @unreleased
 */
export default ({
    isOpen,
    title,
    handleClose,
    handleConfirm,
    className,
}: {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    className?: string;
}) => {
    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={className}
        >
            <>
                <div className={styles.archiveDialogContent}>
                    {__('Are you sure you want to archive your campaign? All forms associated with this campaign will be inaccessible to donors.', 'give')}
                </div>
                <div className={styles.archiveDialogButtons}>
                    <button
                        className={styles.cancelButton}
                        onClick={handleClose}

                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={styles.confirmButton}
                        onClick={handleConfirm}
                    >
                        {__('Archive campaign', 'give')}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
