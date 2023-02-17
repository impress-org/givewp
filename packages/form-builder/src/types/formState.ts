import type {FormSettings} from './formSettings';
import {BlockInstance} from '@wordpress/blocks';

/**
 * @since 0.1.0
 */
export type FormState = {
    blocks: BlockInstance[];
    settings: FormSettings;
    isDirty?: boolean;
};
