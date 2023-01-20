import type {Block} from '../../../types/block'
import type {FormData} from '../../../types/formData'
import type {StorageDriver} from "../interface";
import {FormSettings} from "@givewp/form-builder/types";

const localStorageDriver: StorageDriver = {

    /**
     * Save form builder data (blocks and settings)
     *
     * @param blocks
     * @param formSettings
     */
    save: ({blocks, formSettings}: FormData) => {
        return new Promise<FormSettings>((resolve) => {
            setTimeout(function () {
                localStorage.setItem('@givewp/form-builder.blocks', JSON.stringify(blocks));
                localStorage.setItem('@givewp/form-builder.settings', JSON.stringify(formSettings));
                resolve(formSettings);
            }, 1000);
        });
    },

    /**
     * Load form builder data (blocks and settings)
     */
    load: (): FormData => {
        const blocks = JSON.parse(localStorage.getItem('@givewp/form-builder.blocks'));
        const formSettings = JSON.parse(localStorage.getItem('@givewp/form-builder.settings'));
        return {
            blocks,
            formSettings,
        };
    },

    /**
     * Generate form preview
     *
     * @param template
     * @param blocks
     */
    preview: ({blocks, formSettings}: FormData) => {
        return new Promise<string>((resolve) => {
            setTimeout(function () {
                resolve(JSON.stringify(formSettings) + JSON.stringify(blocks));
            }, 1000);
        });
    }
};

export default localStorageDriver;
