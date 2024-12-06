import {Challenge} from '@givewp/forms/types';

const isValid = async (challenge: Challenge) => challenge.execute((value: any) => {
    challenge.value = value;
});

export default async function validateChallenges(challenges: Challenge[], values: any, setError: any) {
    for (const challenge of challenges) {
        if (!await isValid(challenge)) {
            return false;
        }

        if (challenge.id in values && challenge?.value && challenge?.value !== values[challenge.id]) {
            values[challenge.id] = challenge?.value;
        }
    }

    return true;
}
