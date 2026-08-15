import { createContext, useContext } from '@wordpress/element';
import {FeatureNoticeDialog} from './Dialogs';

export const OnboardingContext = createContext([]);

export interface OnboardingStateProps {
    showFeatureNoticeDialog: boolean;
    showDefaultFormTooltip: boolean;
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
