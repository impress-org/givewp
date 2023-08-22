import {createContext, useContext} from 'react';
import Banner from './Components/Banner';
import {FeatureNoticeDialog} from './Dialogs';

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
    showFeatureNoticeDialog: boolean;
}

export default function Onboarding() {
    const [state, setState] = useContext(OnboardingContext);

    return (
        <>
            {state.showBanner && <Banner />}

            {state.showFeatureNoticeDialog && (
                <FeatureNoticeDialog
                    handleClose={() => setState(prev => ({
                        ...prev,
                        showFeatureNoticeDialog: false
                    }))} />
            )}
        </>
    )
}
