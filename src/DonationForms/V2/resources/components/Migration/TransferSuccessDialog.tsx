import {useState} from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import Input from '@givewp/components/AdminUI/Input';
import {AlertTriangle, CheckCircle} from '@givewp/components/AdminUI/Icons';

import styles from './style.module.scss';

interface DialogStateProps {
    isOpen: boolean;
    step: number;
    showHeader: boolean;
    showCloseIcon: boolean;
    dialogTitle: string;
    dialogIcon: JSX.Element;
}

interface ConfirmDialogStateProps {
    input: string;
    delete: boolean;
    changeUrl: boolean;
    redirect: boolean;
}

function Confirmation({handleTransferConfirmation}) {
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
                onClick={() => handleTransferConfirmation({
                    delete: state.delete,
                    changeUrl: state.changeUrl,
                    redirect: state.redirect
                })}
            >
                {__('Yes, proceed', 'give')}
            </Button>
        </>
    )
}

export default function TransferSuccessDialog({handleClose, formName, formId}) {
    const [state, setState] = useState<DialogStateProps>({
        isOpen: true,
        step: 0,
        showHeader: true,
        showCloseIcon: true,
        dialogTitle: __('Transfer existing donation data', 'give'),
        dialogIcon: <AlertTriangle />
    });

    function handleTransferConfirmation(params) {
        fetch(window.GiveDonationForms.apiRoot, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.GiveDonationForms.apiNonce
            },
            body: JSON.stringify(params)
        }).then((response) => {
            setState(prev => ({
                ...prev,
                showHeader: false,
                showCloseIcon: false,
                step: response.ok ? 2 : 3,
                dialogIcon: response.ok ? <CheckCircle /> : <AlertTriangle />
            }))
        })
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
            <div className={cx(styles.title, styles.center)}>
                {__('Transfer completed successfully', 'give')}
            </div>

            <div className={styles.center}>
                {__('Your donation data was successfully transferred to the new v3 form created.', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={() => setState(prev => ({
                    ...prev,
                    isOpen: false,
                }))}
                style={{width: '100%'}}
            >
                {__('Go back to your donation form list', 'give')}
            </Button>
        </>
    )

    const Error = () => (
        <>
            <div className={cx(styles.title, styles.center)}>
                {__('Transfer not completed!', 'give')}
            </div>

            <div className={styles.center}>
                {__('Something went wrong with the transfer.', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={handleClose}
                style={{width: '100%'}}
            >
                {__('Go back to your donation form list', 'give')}
            </Button>
        </>
    )

    const Screen = () => {
        switch (state.step) {
            case 1:
                return <Confirmation handleTransferConfirmation={handleTransferConfirmation} />;
            case 2:
                return <Completed />;
            case 3:
                return <Error />;
            default:
                return <Notice />;
        }
    }

    return (
        <ModalDialog
            isOpen={state.isOpen}
            icon={state.dialogIcon}
            showHeader={state.showHeader}
            showCloseIcon={state.showCloseIcon}
            handleClose={handleClose}
            title={state.dialogTitle}
        >
            <Screen />
        </ModalDialog>
    );

}
