import type {StorageDriver} from './interface';
import restApiStorageDriver from '@givewp/form-builder/common/storage/drivers/rest-api';

declare global {
    interface Window {
        storage?: StorageDriver;
    }
}

const Storage: StorageDriver = restApiStorageDriver;

export default Storage;
