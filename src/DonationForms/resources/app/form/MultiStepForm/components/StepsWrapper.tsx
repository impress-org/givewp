import {ReactNode} from 'react';
import PreviousButton from '@givewp/forms/app/form/MultiStepForm/components/PreviousButton';
import {__, sprintf} from '@wordpress/i18n';
import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';
import {StepObject} from '@givewp/forms/app/form/MultiStepForm/types';
import getCurrentStepObject from '@givewp/forms/app/form/MultiStepForm/utilities/getCurrentStepObject';
import {Label, ProgressBar} from "react-aria-components";

/**
 * @since 4.3.0 update step title element to h3. add react-aria progress element wrapper..
 * @since 3.4.0 updated with steps props and showStepsHeader conditional
 * @since 3.0.0
 */
export default function StepsWrapper({steps, children}: {steps: StepObject[]; children: ReactNode}) {
    const {currentStep} = useDonationFormMultiStepState();
    const {showHeader: hasFirstStep} = useDonationFormSettings();
    const currentStepObject = getCurrentStepObject(steps, currentStep);

    const totalSteps = hasFirstStep ? steps.length : steps.length - 1;
    const showStepsHeader = !hasFirstStep || currentStepObject.title !== null;

    return (
        <div className="givewp-donation-form__steps">
            {showStepsHeader && (
                <>
                    <header className="givewp-donation-form__steps-header">
                        <div className="givewp-donation-form__steps-header-previous">
                            <PreviousButton>{__('Previous', 'give')}</PreviousButton>
                        </div>
                        <div className="givewp-donation-form__steps-header-title">
                            <h3 className="givewp-donation-form__steps-header-title-text">{currentStepObject.title}</h3>
                        </div>
                    </header>
                    <ProgressBar value={currentStep}>
                        <Label className={'sr-only'}>{sprintf(__('Progress: Step %1$d of %2$d', 'give'), currentStep, totalSteps)}</Label>
                        <progress className="givewp-donation-form__steps-progress" value={currentStep} max={totalSteps} />
                    </ProgressBar>
                </>
            )}
            <div className="givewp-donation-form__steps-body">{children}</div>
            <footer className="givewp-donation-form__steps-footer">
                <div className="givewp-donation-form__steps-footer-secure">
                    <i className="fas fa-lock givewp-donation-form__steps-footer-secure-icon"></i>
                    <span className="givewp-donation-form__steps-footer-secure-text">
                        {__('100% Secure Donation', 'give')}
                    </span>
                </div>
            </footer>
        </div>
    );
}
