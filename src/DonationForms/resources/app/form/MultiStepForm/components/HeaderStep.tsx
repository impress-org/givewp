import Header from '@givewp/forms/app/form/Header';
import {__} from '@wordpress/i18n';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import {useDonationFormMultiStepStateDispatch} from '@givewp/forms/app/form/MultiStepForm/store';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';

/**
 * @since 3.0.0
 */
export default function HeaderStep({form}) {
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();
    const {multiStepFirstButtonText} = useDonationFormSettings();

    return (
        <div>
            <Header form={form} />
            <button
                type="button"
                className="givewp-donation-form__steps-button-next"
                onClick={() => {
                    dispatchMultiStep(setCurrentStep(1));
                }}
            >
                {multiStepFirstButtonText ?? __('Donate', 'give')}
            </button>
        </div>
    );
}
