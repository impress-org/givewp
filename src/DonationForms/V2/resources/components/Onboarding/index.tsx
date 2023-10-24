import {createContext, useContext} from 'react';
import Banner from './Components/Banner';
import {FeatureNoticeDialog} from './Dialogs';

export const OnboardingContext = createContext([]);

export interface OnboardingStateProps {
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
                    isEditing={false}
                    isUpgrading={false}
                    handleClose={() => setState(prev => ({
                        ...prev,
                        showFeatureNoticeDialog: false
                    }))} />
            )}
        </>
    )
}
