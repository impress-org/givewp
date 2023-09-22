import {CSSProperties} from 'react';
import {__} from '@wordpress/i18n';
import {ExitIcon} from '@givewp/components/AdminUI/Icons'

import {useFormState, useFormStateDispatch, setTransferState} from '@givewp/form-builder/stores/form-state';

const containerStyles = {
    zIndex: 99999999,
    position: 'fixed',
    bottom: 0,
    left: '15%',
    right: '15%',
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'var(--givewp-blue-500)',
    color: '#fff',
    padding: 'var(--givewp-spacing-2) var(--givewp-spacing-6)',
    fontWeight: 500,
    fontSize: '0.875rem',
    lineHeight: '1.5rem',
    gap: 'var(--givewp-spacing-6)'
} as CSSProperties;

const buttonStyles = {
    all: 'unset',
    fontWeight: 'bold',
    color: 'var(--givewp-grey-900)',
    backgroundColor: '#fff',
    cursor: 'pointer',
    borderRadius: '4px',
    padding: 'var(--givewp-spacing-2) var(--givewp-spacing-4)',
} as CSSProperties;

const closeIconContainerStyles = {
    display: 'flex',
    alignItems: 'center',
} as CSSProperties;

const closeIconStyles = {
    fill: '#fff',
    width: '18px',
    height: '18px',
    cursor: 'pointer'
} as CSSProperties;


export default function TransferNotice() {
    const {transfer} = useFormState();
    const dispatch = useFormStateDispatch();

    const {transferActionUrl, formId} = window.migrationOnboardingData;

    if (!transfer.showNotice) {
        return null;
    }

    return (
        <div style={containerStyles}>
            <div style={{flex: 1}}>
                {__('Once you\'re happy with your new form, permanently transfer your existing donation data to this new form.', 'give')}
            </div>
            <div>
                <button
                    style={buttonStyles}
                    onClick={() => dispatch(setTransferState({showTransferModal: true}))}
                >
                    {__('Transfer data', 'give')}
                </button>
            </div>
            <div style={closeIconContainerStyles}>
                <ExitIcon
                    style={closeIconStyles}
                    onClick={() => {
                        dispatch(setTransferState({showNotice: false}))
                        fetch(transferActionUrl + `&formId=${formId}`, {method: 'POST'})
                    }}
                />
            </div>
        </div>
    )
}
