import {Challenge} from '@givewp/forms/types';

/**
 * @unreleased
 */
interface ChallengeRegistrar {
    register(challenge: Challenge): void;

    getAll(): Challenge[];

    get(id: string): Challenge | undefined;
}

/**
 * @unreleased
 */
export default class Registrar implements ChallengeRegistrar {
    /**
     * @unreleased
     */
    private challenges: Challenge[] = [];

    /**
     * @unreleased
     */
    public get(id: string): Challenge | undefined {
        return this.challenges.find((challenge) => challenge.id === id);
    }

    /**
     * @unreleased
     */
    public getAll(): Challenge[] {
        return this.challenges;
    }

    /**
     * @unreleased
     */
    public register(challenge: Challenge): void {
        if (challenge.hasOwnProperty('initialize')) {
            try {
                challenge.initialize();
            } catch (e) {
                console.error(`Error initializing ${challenge.id} gateway:`, e);
            }
        }

        this.challenges.push(challenge);
    }
}
