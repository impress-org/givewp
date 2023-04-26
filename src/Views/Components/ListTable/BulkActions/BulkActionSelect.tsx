import pageStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import styles from './BulkActionSelect.module.scss';
import {__} from '@wordpress/i18n';
import Select from '@givewp/components/ListTable/Select';
import {useState} from 'react';

let selected = '';

export const BulkActionSelect = ({bulkActions = null, showModal, data, parameters}) => {
    const [selectedState, setSelectedState] = useState(selected);

    if (window.GiveDonations && window.GiveDonations.addonsBulkActions) {
        bulkActions = [...bulkActions, ...window.GiveDonations.addonsBulkActions];
    }

    if (!bulkActions) {
        return null;
    }

    const changeSelected = (event) => {
        selected = event.target.value;
        setSelectedState(event.target.value);
    };

    return (
        <form id={styles.bulkActionsForm} onSubmit={showModal}>
            <Select name="giveListTableBulkActions" value={selectedState} onChange={changeSelected}>
                <option value="">{__('Bulk Actions', 'give')}</option>
                {bulkActions.map((action) => {
                    if (typeof action?.isVisible == 'function' && !action.isVisible(data, parameters)) {
                        return null;
                    }
                    return (
                        <option key={action.value} value={action.value}>
                            {action.label}
                        </option>
                    );
                })}
            </Select>
            <button className={pageStyles.addFormButton}>{__('Apply', 'give')}</button>
        </form>
    );
};
