import {useContext} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {OnboardingContext} from '../DonationFormsListTable'
import Banner from './Banner';
import Toast from '@givewp/components/AdminUI/Toast';
import MigrationSuccessDialog from './MigrationSuccessDialog';
import TransferSuccessDialog from './TransferSuccessDialog';
import FeatureNoticeDialog from './FeatureNoticeDialog';

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
