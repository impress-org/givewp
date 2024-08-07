import {Challenge} from '@givewp/forms/types';
import {__} from '@wordpress/i18n';

const isValid = async (challenge: Challenge) => challenge.execute((value: any) => {
    challenge.value = value;
});

export default async function validateChallenges(challenges: Challenge[], values: any, setError: any) {
    for (const challenge of challenges) {
        if (!await isValid(challenge)) {
            setError('FORM_ERROR', {
                message: __('You must be a human to submit this form.', 'give')
            });
        }

        if (challenge.id in values && challenge?.value !== values[challenge.id]) {
            values[challenge.id] = challenge?.value;
        }
    }
}
