import {getSubmittedEmail, isValidEmail} from './validation';

// Minimal stand-in for a form node so the helper can be tested without a DOM.
const fakeForm = (fieldName, domValue) => ({
    elements: {
        [fieldName]: {value: domValue},
    },
});

describe('isValidEmail', () => {
    it('accepts a well formed address', () => {
        expect(isValidEmail('donor@example.com')).toBe(true);
    });

    it('rejects empty or malformed values', () => {
        expect(isValidEmail('')).toBe(false);
        expect(isValidEmail('donor')).toBe(false);
        expect(isValidEmail('donor@example')).toBe(false);
        expect(isValidEmail(null)).toBe(false);
    });
});

describe('getSubmittedEmail', () => {
    it('reads the autofilled DOM value even when the state value is empty', () => {
        expect(getSubmittedEmail(fakeForm('email', 'autofilled@example.com'), 'email', '')).toBe(
            'autofilled@example.com'
        );
    });

    it('falls back to the state value when the field is empty', () => {
        expect(getSubmittedEmail(fakeForm('email', ''), 'email', 'typed@example.com')).toBe('typed@example.com');
    });

    it('trims surrounding whitespace', () => {
        expect(getSubmittedEmail(fakeForm('email', '  spaced@example.com  '), 'email', '')).toBe('spaced@example.com');
    });

    it('returns an empty string when nothing is available', () => {
        expect(getSubmittedEmail(fakeForm('email', ''), 'email', '')).toBe('');
        expect(getSubmittedEmail(null, 'email', '')).toBe('');
    });
});
