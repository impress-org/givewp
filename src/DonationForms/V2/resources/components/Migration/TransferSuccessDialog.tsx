import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';

import styles from './style.module.scss';

export default function TransferSuccessDialog({handleClose, formId}) {
    return (
        <ModalDialog
            isOpen={true}
            title={__('Transfer existing donation data', 'give')}
            handleClose={handleClose}
        >
            <div className={styles.title}>
                {__('Transferring donation data is not reversible', 'give')}
            </div>

            <div>
                {__('Transferring donations involves moving all donations from the v2 form [form name] to the v3 form, leaving no donations associated with the v2 form after the transfer.', 'give')}
            </div>

            <Button
                size="large"
                onClick={() => console.log(formId)}
            >
                {__('Transfer', 'give')}
            </Button>

        </ModalDialog>
    )
}
