import {createContext, useContext} from 'react';
import {FeatureNoticeDialog} from './Dialogs';

export const OnboardingContext = createContext([]);

export interface OnboardingStateProps {
    showFeatureNoticeDialog: boolean;
    showDefaultFormTooltip: boolean;
}

/**
 * @since TBD Return nothing while no dialog shows, so the list page renders no empty banner landmark.
 */
export default function Onboarding() {
    const [state, setState] = useContext(OnboardingContext);

    if (!state.showFeatureNoticeDialog) {
        return null;
    }

    return (
        <FeatureNoticeDialog
            isEditing={false}
            isUpgrading={false}
            handleClose={() => setState(prev => ({
                ...prev,
                showFeatureNoticeDialog: false
            }))} />
    );
}
