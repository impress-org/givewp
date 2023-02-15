import type {Block} from './block';
import type {FormSettings} from './formSettings';

/**
 * @since 0.1.0
 */
export type FormState = {
    blocks: Block[];
    settings: FormSettings;
    isDirty?: boolean;
};
