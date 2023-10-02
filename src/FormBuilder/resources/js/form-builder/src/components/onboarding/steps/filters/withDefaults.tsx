const withDefaults = (steps) => {
    return steps.map((step) => {
        return {...step,...{
                canClickTarget: false,
                scrollTo: false,
                cancelIcon: {
                    enabled: false,
                },
                arrow: false,
            }}
    })
}

export default withDefaults
