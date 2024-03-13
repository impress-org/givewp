import {__} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';

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
