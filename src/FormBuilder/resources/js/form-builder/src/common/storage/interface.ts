import {FormData} from '../../types/formData';
import {FormSettings} from '@givewp/form-builder/types';

export interface StorageDriver {
    save({blocks, formSettings}: FormData): Promise<FormSettings>;

    load(): FormData;

    preview({blocks, formSettings}: FormData): Promise<string>;
}
