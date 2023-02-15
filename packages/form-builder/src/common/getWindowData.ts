import {FormDesign, FormPageSettings} from '@givewp/form-builder/types';

declare global {
    interface Window {
        storageData?: {
            formDesigns: FormDesign[];
            formPage: FormPageSettings;
            currency: string;
        };
    }
}

export default function getWindowData() {
    return window.storageData;
}
