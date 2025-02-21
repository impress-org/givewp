import {__} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';

export function CampaignsRowActions({item, setUpdateErrors, parameters}) {
    return (
        <RowAction
            href={`edit.php?post_type=give_forms&page=give-campaigns&id=${item.id}`}
            displayText={__('Edit', 'give')}
        />
    );
}
