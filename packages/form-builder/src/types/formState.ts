import type {Block} from './block';
import type {FormSettings} from './formSettings';

/**
 * @unreleased
 */
export type FormState = {
    blocks: Block[];
    settings: FormSettings;
};
