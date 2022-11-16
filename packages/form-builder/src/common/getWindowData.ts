import {FormDesign} from '@givewp/form-builder/types';

declare global {
    interface Window {
        storageData?: {
            formDesigns: FormDesign[];
        };
    }
}

export default function getWindowData() {
    return window.storageData;
}
