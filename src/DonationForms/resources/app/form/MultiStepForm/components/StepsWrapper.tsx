import {ReactNode} from "react";
import PreviousButton from "@givewp/forms/app/form/MultiStepForm/components/PreviousButton";
import StepsPagination from "@givewp/forms/app/form/MultiStepForm/components/StepsPagination";
import {__} from "@wordpress/i18n";
import useCurrentStep from "@givewp/forms/app/form/MultiStepForm/hooks/useCurrentStep";
import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';

/**
 * @since 3.0.0
 */
function StepsWrapperTitle() {
    const step = useCurrentStep();

    return step.title && <p className="givewp-donation-form__steps-header-title-text">{step.title}</p>;
}

/**
 * @since 3.0.0
 */
export default function StepsWrapper({children}: { children: ReactNode }) {
    const {steps, currentStep} = useDonationFormMultiStepState();
    const {showHeader} = useDonationFormSettings();

    const showProgress = !showHeader || currentStep > 0;
    const totalSteps = showHeader ? steps.length : steps.length - 1;
    const currentStepProgress = !showHeader && currentStep === 0 ? totalSteps / 10 : currentStep;
    const showStepsHeader = !showHeader || currentStep > 0;

    return (
        <div className="givewp-donation-form__steps">
          {showStepsHeader && (
            <div className="givewp-donation-form__steps-header">
                <div className="givewp-donation-form__steps-header-previous">
                    <PreviousButton>{__('Previous', 'give')}</PreviousButton>
                </div>
                <div className="givewp-donation-form__steps-header-title">
                    <StepsWrapperTitle />
                </div>
            </div>
            )}
            {showProgress && (
                <progress className="givewp-donation-form__steps-progress" value={currentStepProgress} max={totalSteps} />
            )}
            <div className="givewp-donation-form__steps-body">{children}</div>
            <div className="givewp-donation-form__steps-footer">
                <div className="givewp-donation-form__steps-footer-secure">
                    <i className="fas fa-lock givewp-donation-form__steps-footer-secure-icon"></i>
                    <small className="givewp-donation-form__steps-footer-secure-icon">
                        {__('Secure Donation', 'give')}
                    </small>
                </div>
            </div>
        </div>
    );
}
