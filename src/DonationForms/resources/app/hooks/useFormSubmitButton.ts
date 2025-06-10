/**
 * @since 4.1.1 updated the selector to use the next button classname
 * @since 4.0.0
 */
export default function useFormSubmitButton(): HTMLButtonElement | null {
    const donationFormWithSubmitButton = Array.from(document.forms).pop();

    const submitButton: HTMLButtonElement = donationFormWithSubmitButton?.querySelector('[type="submit"]');
    if (submitButton) {
        return submitButton;
    }

    const nextButton: HTMLButtonElement = donationFormWithSubmitButton?.querySelector(
        '.givewp-donation-form__steps-button-next[type="button"]'
    );
    if (nextButton) {
        return nextButton;
    }

    return null;
}
