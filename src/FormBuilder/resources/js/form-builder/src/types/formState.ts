import type {FormSettings} from './formSettings';
import type {TransferState} from './transferState';
import {BlockInstance} from '@wordpress/blocks';

/**
 * @since 3.0.0
 */
export type FormState = {
    blocks: BlockInstance[];
    settings: FormSettings;
    transfer: TransferState
    isDirty?: boolean;
};
