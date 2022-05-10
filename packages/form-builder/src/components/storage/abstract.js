const abstractStorageDriver = {
    save: () => new Promise((resolve, reject) => {
        reject( new Error('Save is not implemented') )
    }),
    load: () => new Error('Load is not implemented'),
}

export default abstractStorageDriver
