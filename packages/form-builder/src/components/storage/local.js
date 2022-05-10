const localStorageDriver = {
    save: ( blocks ) => {
        console.log( 'Saving to local storage...' )
        console.log( blocks )
        return new Promise((resolve, reject) => {
            setTimeout( function() {
                localStorage.setItem('@givewp/form-builder', JSON.stringify(blocks) )
                console.log( 'Saved to local storage!' )
                resolve()
            }, 1000)
        })
    },
    load: () => {
        const value = localStorage.getItem('@givewp/form-builder' )
        console.log( 'Loading from local storage...' )
        console.log( value )
        return JSON.parse( value )
    },
}

export default localStorageDriver
