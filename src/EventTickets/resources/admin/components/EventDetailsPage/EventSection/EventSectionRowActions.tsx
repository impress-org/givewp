import {__} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';

/**
 * @since 3.6.0
 */
export function EventSectionRowActions({event, openEditModal}) {
    return () => {
        return (
                <RowAction onClick={openEditModal} displayText={__('Edit', 'give')} />
        );
    };
}
