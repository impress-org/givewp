import {FormData} from '../../types/formData';
import {Block} from '../../types/block';
import {FormSettings} from "@givewp/form-builder/types";

export interface StorageDriver {
    save({blocks, formSettings}: FormData): Promise<FormSettings>;

    load(): FormData;

    preview({blocks, formSettings}: FormData): Promise<string>;
}
