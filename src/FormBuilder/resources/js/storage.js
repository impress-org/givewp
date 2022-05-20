window.storage = {
    save: ( { blocks, formTitle } ) => {
        return new Promise((resolve, reject) => {
            jQuery.post( {
                url: window.storageData.resourceURL,
                headers: {
                    "X-WP-Nonce": window.storageData.nonce,
                },
                data: {
                    blocks: JSON.stringify(blocks),
                    formTitle: formTitle,
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
            formTitle: window.storageData.formTitle,
        };
    },
}
