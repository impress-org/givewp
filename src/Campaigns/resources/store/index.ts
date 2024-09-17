import {register, createReduxStore} from '@wordpress/data';

import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';
import * as controls from './controls';
import reducer from './reducer';

const STORE_NAME = 'givewp/campaigns';

export const store = createReduxStore(STORE_NAME, {
    selectors,
    actions,
    resolvers,
    reducer,
    controls
});

register(store);

export {STORE_NAME};
