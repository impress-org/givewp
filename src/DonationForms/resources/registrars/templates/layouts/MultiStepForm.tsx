import {FormProps} from '@givewp/forms/propTypes';
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
    const FormError = window.givewp.form.templates.layouts.formError;

    return (
        <form {...formProps}>
            {children}

            {formError && (
                <section className="givewp-layouts givewp-layouts-section">
                    <FormError error={formError} />
                </section>
            )}

            {previousButton}
            {nextButton}
            {submitButton}
        </form>
    );
}
