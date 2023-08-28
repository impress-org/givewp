import {useState} from 'react';
import cx from 'classnames';
import {__, sprintf} from '@wordpress/i18n';
import {Interweave} from 'interweave';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import Input from '@givewp/components/AdminUI/Input';
import {AlertTriangle, CheckCircle} from '@givewp/components/AdminUI/Icons';

import styles from '../style.module.scss';

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
}

function Confirmation({handleTransferConfirmation}) {
    const [state, setState] = useState<ConfirmDialogStateProps>({
        input: '',
        delete: false,
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
                {__('Transferring donation data to the upgraded form', 'give')}
            </div>

            <div>
                <Interweave content={__('Type <strong>transfer</strong> to confirm transfer of donation data for the form selected. This means all blocks, shortcodes, and the form URL will automatically redirect to the upgraded form.', 'give')} />
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
                    label={__('Delete the existing form after transfer', 'give')}
                    type="checkbox"
                    checked={state.delete}
                    onChange={e => handleCheckboxChange(e, 'delete')}
                />
            </div>

            <br />

            <Button
                disabled={state.input !== 'transfer'}
                size="large"
                style={{width: '100%'}}
                onClick={() => handleTransferConfirmation({
                    delete: state.delete,
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
        fetch(window.GiveDonationForms.apiRoot + '/transfer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.GiveDonationForms.apiNonce
            },
            body: JSON.stringify({...params, formId})
        }).then((response) => {
            setState(prev => ({
                ...prev,
                showHeader: false,
                showCloseIcon: false,
                step: response.ok ? 2 : 3,
                dialogIcon: response.ok ? <CheckCircle /> : <AlertTriangle />
            }))
        })

       // updateOnboardingOption('transfer_onboarding_completed')
    }

    const Notice = () => (
        <>
            <div className={styles.title}>
                {__('Transferring donation data cannot be undone', 'give')}
            </div>

            <div>
                <Interweave content={__(sprintf( 'Transferring donations involves moving all donations from the existing form %s to the upgraded form, leaving no donations associated with the existing form after the transfer.', `<span class="${styles.formName}">[${formName}]</span>`), 'give')} />
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
                {__('Your donation data was successfully transferred to the upgraded form.', 'give')}
            </div>

            <br /><br />

            <Button
                size="large"
                onClick={() => window.location.href = 'edit.php?post_type=give_forms&page=give-forms'}
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
