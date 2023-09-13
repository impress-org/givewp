import {CSSProperties} from 'react';
import {__} from '@wordpress/i18n';
import {ExitIcon} from '@givewp/components/AdminUI/Icons'

import {useFormState, useFormStateDispatch, setTransferState} from '@givewp/form-builder/stores/form-state';

const containerStyles = {
    zIndex: 99999999,
    position: 'fixed',
    bottom: 0,
    left: '10%',
    right: '10%',
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'var(--givewp-blue-600)',
    color: '#fff',
    padding: '15px',
    fontWeight: 500,
    fontSize: '0.875rem',
    gap: '10px'
} as CSSProperties;

const nextStepStyles = {
    flexGrow: 0,
    fontSize: '0.75rem',
    fontWeight: 'bold',
    lineHeight: '1.33',
    letterSpacing: '0.06px',
    color: 'var(--givewp-blue-600)',
    backgroundColor: '#fff',
    borderRadius: '4px',
    padding: '0.25rem 0.5rem'
} as CSSProperties;

const buttonStyles = {
    all: 'unset',
    fontWeight: 'bold',
    color: '#fff',
    cursor: 'pointer'
} as CSSProperties;

const closeIconContainerStyles = {
    position: 'absolute',
    right: 15
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
            <div>
                <div style={nextStepStyles}>
                    {__('Next step', 'give')}
                </div>
            </div>
            <div>
                {__('When you are satisfied with the new form builder, you can move all donation data from the existing form to this one.', 'give')}
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
