import {FormTemplate} from '@givewp/form-builder/types';

declare global {
    interface Window {
        storageData?: {
            templates: FormTemplate[];
        };
    }
}

export default function getWindowData() {
    return window.storageData;
}
