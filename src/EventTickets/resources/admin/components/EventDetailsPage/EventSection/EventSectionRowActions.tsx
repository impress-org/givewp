import {__} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import EventTicketsApi from '../../api';

const apiSettings = window.GiveEventTicketsDetails;
const eventTicketsApi = new EventTicketsApi(apiSettings);

/**
 * @unreleased
 */
export function EventSectionRowActions({event, openEditModal}) {
    return () => {
        return (
                <RowAction onClick={openEditModal} displayText={__('Edit', 'give')} />
        );
    };
}
