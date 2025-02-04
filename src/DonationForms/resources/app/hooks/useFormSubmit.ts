export default function useFormSubmitButton(): HTMLButtonElement | null {
    const donationFormWithSubmitButton = Array.from(document.forms).pop();

    const submitButton: HTMLButtonElement = donationFormWithSubmitButton.querySelector('[type="submit"]');
    if (submitButton) {
        return submitButton;
    }

    const nextButton: HTMLButtonElement = donationFormWithSubmitButton.querySelector('[type="button"]');
    if (nextButton) {
        return nextButton;
    }

    return null;
}
