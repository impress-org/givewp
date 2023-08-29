import {FormProps} from '@givewp/forms/propTypes';
import {__} from '@wordpress/i18n';
import {ReactNode} from 'react';

interface Props extends FormProps {
    previousButton: ReactNode;
    nextButton: ReactNode;
    submitButton: ReactNode;
}
export default function MultiStepForm({
    children,
    formProps,
    formError,
    previousButton,
    nextButton,
    submitButton,
}: Props) {
    return (
        <form {...formProps}>
            {children}

            {formError && (
                <div style={{textAlign: 'center'}}>
                    <p>{__('The following error occurred when submitting the form:', 'give')}</p>
                    <p>{formError}</p>
                </div>
            )}

            {previousButton}
            {nextButton}
            {submitButton}
        </form>
    );
}
