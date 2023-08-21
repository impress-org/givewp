import {createContext, useContext} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import Banner from './Components/Banner';
import Toast from '@givewp/components/AdminUI/Toast';
import {MigrationSuccessDialog, TransferSuccessDialog, FeatureNoticeDialog} from './Dialogs';
export const OnboardingContext = createContext([]);

export const updateOnboardingOption = async optionName => fetch(window.GiveDonationForms.onboardingApiRoot, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveDonationForms.apiNonce
    },
    body: JSON.stringify({option: optionName})
})

export interface OnboardingStateProps {
    migrationOnboardingCompleted: boolean;
    showBanner: boolean;
    showMigrationSuccessDialog: boolean;
    showTransferSuccessDialog: boolean;
    showFeatureNoticeDialog: boolean;
    showMigrationCompletedToast: boolean;
    formId: number | null;
    formName: string | null;
}

export default function Onboarding() {
    const [state, setState] = useContext(OnboardingContext);

    return (
        <>
            {state.showBanner && <Banner />}

            {!state.migrationOnboardingCompleted && state.showMigrationSuccessDialog && (
                <MigrationSuccessDialog
                    formId={state.formId}
                    handleClose={setState(prev => ({
                        ...prev,
                        showMigrationSuccessDialog: false
                    }))}
                />
            )}

            {state.showTransferSuccessDialog && (
                <TransferSuccessDialog
                    formId={state.formId}
                    formName={state.formName}
                    handleClose={() => setState(prev => ({
                        ...prev,
                        showTransferSuccessDialog: false
                    }))}
                />
            )}

            {state.showFeatureNoticeDialog && (
                <FeatureNoticeDialog
                    handleClose={() => setState(prev => ({
                        ...prev,
                        showFeatureNoticeDialog: false
                    }))} />
            )}

            {state.showMigrationCompletedToast && (
                <Toast
                    type="success"
                    autoClose={6000}
                    handleClose={() => setState(prev => ({
                        ...prev,
                        showMigrationCompletedToast: false
                    }))}>
                    {sprintf(__('Migration of the form "%s" completed successfully', 'give'), state.formName)}
                </Toast>
            )}
        </>
    )
}
