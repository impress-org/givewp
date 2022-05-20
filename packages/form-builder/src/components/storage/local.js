const localStorageDriver = {
    save: ({ blocks, formTitle } ) => {
        return new Promise((resolve, reject) => {
            setTimeout( function() {
                localStorage.setItem('@givewp/form-builder.blocks', JSON.stringify(blocks) )
                localStorage.setItem('@givewp/form-builder.formTitle', formTitle )
                resolve()
            }, 1000)
        })
    },
    load: () => {
        const blocks = JSON.parse( localStorage.getItem('@givewp/form-builder.blocks' ) )
        const formTitle = localStorage.getItem('@givewp/form-builder.formTitle' )
        return {
            blocks,
            formTitle
        }
    },
}

export default localStorageDriver
