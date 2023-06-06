const withDefaults = (defaults) => {
    return (steps) => {
        return steps.map((step) => {
            return {...step,...defaults}
        })
    }
}

export default withDefaults
