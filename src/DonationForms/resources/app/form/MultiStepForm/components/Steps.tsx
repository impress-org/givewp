import {StepObject} from "@givewp/forms/app/form/MultiStepForm/types";
import {useDonationFormMultiStepState} from "@givewp/forms/app/form/MultiStepForm/store";
import usePrevious from "@givewp/forms/app/form/MultiStepForm/hooks/usePreviousValue";
import classNames from "classnames";
import StepsWrapper from "@givewp/forms/app/form/MultiStepForm/components/StepsWrapper";
import { useEffect } from "react";

/**
 * @since 3.0.0
 *
 * This loops through the steps and lazy loads them using a waterfall approach.
 * Only current and previous steps are rendered.  Obviously all previous steps are hidden.
 * This is necessary so the next step is always updated with the form values.
 * The other reason is so gateway scripts remain loaded on the page and are not removed by unmounting the step.
 */
export default function Steps({steps}: { steps: StepObject[] }) {
    const {currentStep} = useDonationFormMultiStepState();
    const previousStep = usePrevious(currentStep);

    /**
     * @since 3.16.0 Scroll to the top of the iframe when the step changes.
     */
    useEffect(() => {
        /* @ts-ignore */
        window.parent.document.getElementById(window.parentIFrame?.getId())?.scrollIntoView()
    }, [currentStep]);

    const stepElements = steps?.map(({id, element}) => {
        const shouldRenderElement = currentStep >= id;
        const isFirstStep = id === 0;
        const isCurrentStep = id === currentStep;
        const ascending = currentStep > previousStep;
        const descending = currentStep < previousStep;

        const stepClasses = classNames('givewp-donation-form__step', {
            'givewp-donation-form__step--start': isFirstStep,
            'givewp-donation-form__step--visible': isCurrentStep,
            'givewp-donation-form__step--hidden': !isCurrentStep,
            'givewp-donation-form__step--ascending': isCurrentStep && ascending,
            'givewp-donation-form__step--descending': isCurrentStep && descending,
        });

        return (
            <div key={id} id={`givewp-donation-form-step-${id}`} className={stepClasses}>
                {shouldRenderElement && element}
            </div>
        );
    });

    return <StepsWrapper steps={steps}>{stepElements}</StepsWrapper>
}
