import {
    useDonationFormMultiStepState,
    useDonationFormMultiStepStateDispatch,
} from '@givewp/forms/app/form/MultiStepForm/store';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import classNames from 'classnames';

/**
 * @unreleased
 */
export default function StepsPagination() {
    const {steps, currentStep} = useDonationFormMultiStepState();
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    const navigation = steps?.map?.(({id}) => {
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