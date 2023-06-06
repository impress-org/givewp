import Header from '@givewp/forms/app/form/Header';
import {__} from '@wordpress/i18n';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import {useDonationFormMultiStepStateDispatch} from '@givewp/forms/app/form/MultiStepForm/store';

/**
 * @unreleased
 */
export default function HeaderStep() {
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    return (
        <div>
            <Header />
            <section className="givewp-layouts givewp-layouts-section">
                <button
                    type="button"
                    onClick={() => {
                        dispatchMultiStep(setCurrentStep(1));
                    }}
                >
                    {__('Donate Now', 'give')}
                </button>
            </section>
        </div>
    );
}