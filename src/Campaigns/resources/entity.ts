import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';
import './store';

//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'campaign',
        kind: 'givewp',
        baseURL: '/give-api/v2/campaigns',
        baseURLParams: {},
        plural: 'campaigns',
        label: __('Campaign', 'give'),
        supportsPagination: true
    }
]);


