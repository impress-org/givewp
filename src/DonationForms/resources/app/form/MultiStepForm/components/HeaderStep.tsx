import Header from '@givewp/forms/app/form/Header';
import {__} from '@wordpress/i18n';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import {useDonationFormMultiStepStateDispatch} from '@givewp/forms/app/form/MultiStepForm/store';

/**
 * @since 3.0.0
 */
export default function HeaderStep() {
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    return (
        <div>
            <Header />
            <button
                type="button"
                className="givewp-donation-form__steps-button-next"
                onClick={() => {
                    dispatchMultiStep(setCurrentStep(1));
                }}
            >
                {__('Donate', 'give')}
            </button>
        </div>
    );
}
