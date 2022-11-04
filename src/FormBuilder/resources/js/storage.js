window.storage = {
    save: ( { blocks, formSettings } ) => {
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
                .done(() => {
                    resolve();
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
            settings: JSON.parse( window.storageData.settings || "{}" ),
        };
    },
    preview: ( template, blocks ) => {
        return new Promise((resolve, reject) => {
            jQuery
                .post({
                    url: window.storageData.previewURL,
                    headers: {
                        'X-WP-Nonce': window.storageData.nonce,
                    },
                    data: {
                        'form-template-id': template,
                        'form-blocks': JSON.stringify(blocks),
                    },
                })
                .then(resolve);
        });
    },
}
