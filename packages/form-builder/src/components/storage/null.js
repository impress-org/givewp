const nullStorageDriver = {
    save: () => {
        return new Promise((resolve, reject) => {
            resolve()
        })
    },
    load: () => null,
}

export default nullStorageDriver
