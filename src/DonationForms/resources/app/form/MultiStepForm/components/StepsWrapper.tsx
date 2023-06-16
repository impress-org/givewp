import {ReactNode} from "react";
import PreviousButton from "@givewp/forms/app/form/MultiStepForm/components/PreviousButton";
import StepsPagination from "@givewp/forms/app/form/MultiStepForm/components/StepsPagination";
import {__} from "@wordpress/i18n";
import useCurrentStep from "@givewp/forms/app/form/MultiStepForm/hooks/useCurrentStep";

/**
 * @since 0.4.0
 */
function StepsWrapperTitle() {
    const step = useCurrentStep();

    return step.id > 0 && <p className="givewp-donation-form__steps-header-title-text">{step.title}</p>;
}

/**
 * @since 0.4.0
 */
export default function StepsWrapper({children}: { children: ReactNode }) {
    return (
        <div className="givewp-donation-form__steps">
            <div className="givewp-donation-form__steps-header">
                <div className="givewp-donation-form__steps-header-previous">
                    <PreviousButton>
                        <i className="fas fa-chevron-left"></i>
                    </PreviousButton>
                </div>
                <div className="givewp-donation-form__steps-header-title">
                    <StepsWrapperTitle />
                </div>
            </div>
            <div className="givewp-donation-form__steps-body">{children}</div>
            <div className="givewp-donation-form__steps-footer">
                <div className="givewp-donation-form__steps-footer-pagination">
                    <StepsPagination />
                </div>
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