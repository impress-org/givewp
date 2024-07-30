import {createContext, useContext} from 'react';
import {FeatureNoticeDialog} from './Dialogs';

export const OnboardingContext = createContext([]);

export interface OnboardingStateProps {
    showFeatureNoticeDialog: boolean;
}

export default function Onboarding() {
    const [state, setState] = useContext(OnboardingContext);

    return (
        <>
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
