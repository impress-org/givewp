import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';

/**
 * Associate orphaned forms to a campaign
 *
 * @unreleased
 */
export default function OrphanedFormsModal({isOpen, setOpen}) {

    const openModal = () => setOpen(true);
    const closeModal = () => {
        setOpen(false);
    };


    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={() => closeModal()}
            title={__('Orphaned forms', 'give')}
        >
            <>

            </>
        </ModalDialog>
    );
}
