import {Challenge} from '@givewp/forms/types';
import {__} from '@wordpress/i18n';

export default async function validateChallenges(challenges: Challenge[], values: any, setError: any) {
    for (const challenge of challenges) {
        if (!await challenge.execute()) {
            setError('FORM_ERROR', {
                message: __('You must be a human to submit this form.', 'give')
            });
        }

        if (challenge.id in values && challenge?.value !== values[challenge.id]) {
            values[challenge.id] = challenge?.value;
        }
    }
}
