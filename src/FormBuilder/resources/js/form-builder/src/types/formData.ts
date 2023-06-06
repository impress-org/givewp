import type {FormSettings} from './formSettings';
import {BlockInstance} from '@wordpress/blocks';

export interface FormData {
    blocks: BlockInstance[];
    formSettings: FormSettings;
}
