import {FormDesign, FormPageSettings} from '@givewp/form-builder/types';

declare global {
    interface Window {
        storageData?: {
            formDesigns: FormDesign[];
            formPage: FormPageSettings;
        };
    }
}

export default function getWindowData() {
    return window.storageData;
}
