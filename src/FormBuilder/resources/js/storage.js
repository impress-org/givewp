window.storage = {
    save: ( { blocks, formSettings } ) => {
        return new Promise((resolve, reject) => {
            jQuery.post( {
                url: window.storageData.resourceURL,
                headers: {
                    "X-WP-Nonce": window.storageData.nonce,
                },
                data: {
                    blocks: JSON.stringify(blocks),
                    settings: JSON.stringify(formSettings),
                }
            })
                .done(() => {
                    resolve()
                })
                .fail(() => {
                    reject(new Error("Save not implemented!!!!"))
                })
        })
    },
    load: () => {
        return {
            blocks: JSON.parse(window.storageData.blockData),
            settings: JSON.parse( window.storageData.settings || "{}" ),
        };
    },
}
