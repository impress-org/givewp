import {__} from '@wordpress/i18n';
import {StorageDriver} from '@givewp/form-builder/common/storage/interface';

// @ts-ignore
const storageData = window.giveStorageData;
// @ts-ignore
const jQuery = window.jQuery;

/**
 * @unreleased
 */
const addLocaleToUrl = (url: string) => {
    const urlParams = new URLSearchParams(window.location.search);
    const locale = urlParams.get('locale');
    const newUrl = new URL(url);
    newUrl.searchParams.set('locale', locale);
    return newUrl.toString();
};

const restApiStorageDriver: StorageDriver = {
    save: ({blocks, formSettings}) => {
        return new Promise((resolve, reject) => {
            jQuery
                .post({
                    url: storageData.resourceURL,
                    headers: {
                        'X-WP-Nonce': storageData.nonce,
                    },
                    data: {
                        blocks: JSON.stringify(blocks),
                        settings: JSON.stringify(formSettings),
                    },
                })
                .done((response) => {
                    resolve(JSON.parse(response.settings));
                })
                .fail((error) => {
                    console.error(error);
                    const cause = {
                        code: error?.responseJSON?.code || 'unknown',
                        message:
                            error?.responseJSON?.message ||
                            __(
                                'An unknown error has occurred. Please try saving again and contact support if the problem persists.',
                                'give'
                            ),
                        status: error?.responseJSON?.data?.status || 500,
                    };
                    reject(new Error(cause.message, {cause}));
                });
        });
    },
    load: () => {
        return {
            blocks: JSON.parse(storageData.blockData),
            formSettings: JSON.parse(storageData.settings || '{}'),
        };
    },
    preview: ({blocks, formSettings}) => {
        return new Promise((resolve, reject) => {
            jQuery
                .post({
                    url: addLocaleToUrl(storageData.previewURL),
                    headers: {
                        'X-WP-Nonce': storageData.nonce,
                    },
                    data: {
                        'form-blocks': JSON.stringify(blocks),
                        'form-settings': JSON.stringify(formSettings),
                    },
                })
                .then(resolve);
        });
    },
};

export default restApiStorageDriver;
