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
    dialogTitle: string;
    dialogIcon: JSX.Element;
}

interface ConfirmDialogStateProps {
    input: string;
    delete: boolean;
    changeUrl: boolean;
    redirect: boolean;
}

function Confirmation({handleConfirmation}) {
    const [state, setState] = useState<ConfirmDialogStateProps>({
        input: '',
        delete: false,
        changeUrl: false,
        redirect: false
    });
    function handleInputChange(e) {
        e.persist();
        setState(prev => ({...prev, input: e.target.value}))
    }

    function handleCheckboxChange(e, name) {
        e.persist();
        setState(prev => ({...prev, [name]: e.target.checked}))
    }

    return (
        <>
            <div className={styles.title}>
                {__('Transferring donation data to associated v3 forms', 'give')}
            </div>

            <div>
                {__('Type', 'give')} <strong>transfer</strong> {__('to confirm transfer of donation data for the form selected', 'give')}
            </div>

            <br />

            <div>
                <Input
                    value={state.input}
                    onChange={handleInputChange}
                />
            </div>

            <br />

            <div className={styles.checkbox}>
                <Input
                    label={__('Delete the v2 form after transfer', 'give')}
                    type="checkbox"
                    checked={state.delete}
                    onChange={e => handleCheckboxChange(e, 'delete')}
                />
            </div>
            <div className={styles.checkbox}>
                <Input
                    label={__('Change the form URL to point to the v3 form', 'give')}
                    type="checkbox"
                    checked={state.changeUrl}
                    onChange={e => handleCheckboxChange(e, 'changeUrl')}
                />
            </div>
            <div className={styles.checkbox}>
                <Input
                    label={__('Redirect v2 form shortcodes and blocks to v3.', 'give')}
                    type="checkbox"
                    checked={state.redirect}
                    onChange={e => handleCheckboxChange(e, 'redirect')}
                />
            </div>

            <br />

            <Button
                disabled={state.input !== 'transfer'}
                size="large"
                style={{width: '100%'}}
                onClick={() => handleConfirmation(state.delete, state.changeUrl, state.redirect)}
            >
                {__('Yes, proceed', 'give')}
            </Button>
        </>
    )
}

export default function TransferSuccessDialog({handleClose, formName, formId}) {
    const [state, setState] = useState<DialogStateProps>({
        step: 0,
        showHeader: true,
        dialogTitle: __('Transfer existing donation data', 'give'),
        dialogIcon: <AlertTriangle />
    });

    function handleConfirmation(deleteForms, changeUrl, redirect) {
        // make request


        setState(prev => ({
            ...prev,
            step: 2,
            showHeader: false,
            dialogTitle: __('Success', 'give')
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


    const Completed = () => (
        <>
            <div className={styles.title}>
                {__('Completed', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={() => console.log(formId)}
                style={{width: '100%'}}
            >
                {__('Close', 'give')}
            </Button>
        </>
    )

    const Screen = () => {
        switch (state.step) {
            case 1:
                return <Confirmation handleConfirmation={handleConfirmation} />;
            case 2:
                return <Completed />;
            default:
                return <Notice />;
        }
    }

    return (
        <ModalDialog
            icon={state.dialogIcon}
            showHeader={state.showHeader}
            handleClose={handleClose}
            title={state.dialogTitle}
        >
            <Screen />
        </ModalDialog>
    );

}
