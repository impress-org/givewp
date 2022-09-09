import {__, sprintf} from '@wordpress/i18n';

export default function getFieldErrorMessages() {
    const message = sprintf(
        /* translators: base error message */
        __('%s is required.', 'give`'),
        `{#label}`
    );

    return {
        'string.base': message,
        'string.empty': message,
    };
}
