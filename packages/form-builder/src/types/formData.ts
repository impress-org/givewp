import type {Block} from './block';
import type {FormSettings} from './formSettings';

export interface FormData {
    blocks: Block[];
    formSettings: FormSettings;
}
