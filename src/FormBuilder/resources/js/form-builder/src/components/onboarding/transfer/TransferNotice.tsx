import {__} from '@wordpress/i18n';
import {ExitIcon} from '@givewp/components/AdminUI/Icons'
import {setTransferState, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

export default function TransferNotice() {
    const {transfer} = useFormState();
    const dispatch = useFormStateDispatch();

    const {transferActionUrl, formId} = window.migrationOnboardingData;

    if (!transfer.showNotice) {
        return null;
    }

    return (
        <div className="givewp-transfer-notice-container">
            <div style={{flex: 1}}>
                {__('Once you\'re happy with your new form, permanently transfer your existing donation data to this new form.', 'give')}
            </div>
            <div>
                <button
                    className="givewp-transfer-button"
                    onClick={() => dispatch(setTransferState({showTransferModal: true}))}
                >
                    {__('Transfer data', 'give')}
                </button>
            </div>
            <div className="givewp-transfer-close-icon-container">
                <ExitIcon
                    onClick={() => {
                        dispatch(setTransferState({showNotice: false, showTooltip: true}))
                        fetch(transferActionUrl + `&formId=${formId}`, {method: 'POST'})
                        document.getElementById('FormBuilderSidebarToggle')?.click();
                    }}
                />
            </div>
        </div>
    )
}
