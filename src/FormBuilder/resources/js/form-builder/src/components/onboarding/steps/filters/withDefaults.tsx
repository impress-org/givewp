import {offset} from '@floating-ui/dom';

const withDefaults = (steps) => {
    return steps.map((step) => {
        return {...step,...{
                canClickTarget: false,
                scrollTo: false,
                cancelIcon: {
                    enabled: false,
                },
                arrow: false,
                floatingUIOptions: {
                    middleware: [offset(20)],
                }
            }}
    })
}

export default withDefaults
