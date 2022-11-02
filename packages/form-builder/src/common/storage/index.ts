import LocalStorage from './drivers/local.ts';
import type {StorageDriver} from "./interface";

declare global {
    interface Window {
        storage?: StorageDriver
    }
}

const Storage: StorageDriver = window.storage ?? LocalStorage;

export default Storage;
