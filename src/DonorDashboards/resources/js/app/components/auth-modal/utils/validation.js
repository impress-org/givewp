// Basic email shape check for front-end validation before hitting the API.
export const isValidEmail = (value) => {
    return typeof value === 'string' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
};

// Prefer the submitted DOM value so browser autofill is respected even when it
// does not fire React's onChange, falling back to the controlled state value.
export const getSubmittedEmail = (form, fieldName, stateValue = '') => {
    const field = form && form.elements ? form.elements[fieldName] : null;
    const domValue = field && typeof field.value === 'string' ? field.value : '';

    return (domValue || stateValue || '').trim();
};
