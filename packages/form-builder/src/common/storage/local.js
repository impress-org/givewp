const localStorageDriver = {
    save: ({blocks, formSettings}) => {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                localStorage.setItem('@givewp/form-builder.blocks', JSON.stringify(blocks));
                localStorage.setItem('@givewp/form-builder.settings', JSON.stringify(formSettings));
                resolve();
            }, 1000);
        });
    },
    load: () => {
        const blocks = JSON.parse(localStorage.getItem('@givewp/form-builder.blocks'));
        const settings = JSON.parse(localStorage.getItem('@givewp/form-builder.settings'));
        return {
            blocks,
            settings,
        };
    },
};

export default localStorageDriver;
