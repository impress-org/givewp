import {createReduxStore} from '@wordpress/data';
import {getWindowData} from '@givewp/form-builder/common';

const DEFAULT_STATE = getWindowData();

const store = createReduxStore('givewp/core', {
    reducer: (state = DEFAULT_STATE) => state,
    selectors: {
        get: () => () => DEFAULT_STATE,
    },
});

export default store;
