import {FormData} from '../../types/formData';
import {Block} from '../../types/block';

export interface StorageDriver {
    save({blocks, formSettings}: FormData): Promise<void>;

    load(): FormData;

    preview({blocks, formSettings}: FormData): Promise<string>;
}
