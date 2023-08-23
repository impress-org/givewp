import {
    useDonationFormMultiStepState,
    useDonationFormMultiStepStateDispatch,
} from '@givewp/forms/app/form/MultiStepForm/store';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import classNames from 'classnames';

/**
 * @since 3.0.0
 */
export default function StepsPagination() {
    const {steps, currentStep} = useDonationFormMultiStepState();
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    if (steps.length <= 1) {
        return null;
    }

    const navigation = steps
        .filter((step) => step.isVisible)
        .map(({id}) => {
            const isNextStep = currentStep <= id;

            return (
                <button
                    key={id}
                    className={classNames('givewp-donation-form__steps-pagination-button', {
                        'givewp-donation-form__steps-pagination-button--current': currentStep === id,
                    })}
                    type="button"
                    disabled={isNextStep}
                    onClick={() => !isNextStep && dispatchMultiStep(setCurrentStep(id))}
                />
            );
        });

    return <div className="givewp-donation-form__steps-pagination">{navigation}</div>;
}
