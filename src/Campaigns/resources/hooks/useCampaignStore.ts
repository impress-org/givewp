import {useSelect, useDispatch} from '@wordpress/data';
import {STORE_NAME} from '../store'

//todo: add return functions interface
export const useCampaignStore = () => {
    const store = useSelect(select => select(STORE_NAME), []);
    const actions = useDispatch(STORE_NAME);

    return {
        store,
        actions
    }
}
