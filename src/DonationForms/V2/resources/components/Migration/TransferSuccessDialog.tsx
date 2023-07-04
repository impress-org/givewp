import {useState} from 'react';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import Input from '@givewp/components/AdminUI/Input';

import {AlertTriangle} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

interface DialogStateProps {
    step: number;
    showHeader: boolean;
    confirmationString: string;
    dialogTitle: string;
}

export default function TransferSuccessDialog({handleClose, formName, formId}) {
    const [state, setState] = useState<DialogStateProps>({
        step: 0,
        showHeader: true,
        confirmationString: '',
        dialogTitle: __('Transfer existing donation data', 'give')
    });

    const handleInputChange = (event) => {
        event.persist();
        setState(prev => ({
            ...prev,
            confirmationString: event.target.value
        }))
    }

    const Notice = () => (
        <>
            <div className={styles.title}>
                {__('Transferring donation data is not reversible', 'give')}
            </div>

            <div>
                {__('Transferring donations involves moving all donations from the v2 form', 'give')} <span
                className={styles.formName}>[{formName}]</span> {__('to the v3 form, leaving no donations associated with the v2 form after the transfer.', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={() => {
                    setState(prev => ({
                        ...prev,
                        step: 1,
                        dialogTitle: __('Confirm transfer', 'give')
                    }))
                }}
                style={{width: '100%'}}
            >
                {__('Transfer', 'give')}
            </Button>
        </>
    )

    const Confirmation = () => (
        <>
            <div className={styles.title}>
                {__('Transferring donation data to associated v3 forms', 'give')}
            </div>

            <div>
                {__('Type', 'give')}
                <strong>transfer</strong> {__('to confirm transfer of donation data for the form selected', 'give')}
            </div>

            <br />

            <div>
                <Input
                    value={state.confirmationString}
                    onChange={handleInputChange}
                />
            </div>

            <br />

            <Button
                disabled={state.confirmationString !== 'transfer'}
                size="large"
                onClick={() => console.log(formId)}
                style={{width: '100%'}}
            >
                {__('Yes, proceed', 'give')}
            </Button>
        </>
    )

    const Completed = () => (
        <>
            <div className={styles.title}>
                {__('Transferring donation data is not reversible', 'give')}
            </div>

            <div>
                {__('Transferring donations involves moving all donations from the v2 form', 'give')} <span
                className={styles.formName}>[{formName}]</span> {__('to the v3 form, leaving no donations associated with the v2 form after the transfer.', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={() => console.log(formId)}
                style={{width: '100%'}}
            >
                {__('Transfer', 'give')}
            </Button>
        </>
    )

    const Screen = () => {
        switch (state.step) {
            case 1:
                return <Confirmation />;
            case 2:
                return <Completed />;
            default:
                return <Notice />;
        }
    }

    return (
        <ModalDialog
            icon={<AlertTriangle />}
            showHeader={state.showHeader}
            handleClose={handleClose}
            title={state.dialogTitle}
        >
            <Screen />
        </ModalDialog>
    );

}
