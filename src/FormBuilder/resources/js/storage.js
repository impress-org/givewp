window.storage = {
    save: ({blocks, formSettings}) => {
        return new Promise((resolve, reject) => {
            jQuery
                .post({
                    url: window.storageData.resourceURL,
                    headers: {
                        'X-WP-Nonce': window.storageData.nonce,
                    },
                    data: {
                        blocks: JSON.stringify(blocks),
                        settings: JSON.stringify(formSettings),
                    },
                })
                .done(( response ) => {
                    resolve(JSON.parse(response.settings));
                })
                .fail((error) => {
                    console.error(error);
                    reject(new Error(error?.responseJSON?.message));
                });
        });
    },
    load: () => {
        return {
            blocks: JSON.parse(window.storageData.blockData),
            formSettings: JSON.parse(window.storageData.settings || '{}'),
        };
    },
    preview: ({blocks, formSettings}) => {
        return new Promise((resolve, reject) => {
            jQuery
                .post({
                    url: window.storageData.previewURL,
                    headers: {
                        'X-WP-Nonce': window.storageData.nonce,
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
