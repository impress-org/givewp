import pageStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import styles from './BulkActionSelect.module.scss';
import {__} from '@wordpress/i18n';
import Select from '@givewp/components/ListTable/Select';

export const BulkActionSelect = ({bulkActions = null, selectedState, showModal, data, parameters}) => {
    const [selectedAction, setSelectedAction] = selectedState;

    if (window.GiveDonations && window.GiveDonations.addonsBulkActions) {
        bulkActions = [...bulkActions, ...window.GiveDonations.addonsBulkActions];
    }

    if (!bulkActions) {
        return null;
    }

    const changeSelected = (event) => {
        setSelectedAction(event.target.value);
    };

    return (
        <form id={styles.bulkActionsForm} onSubmit={showModal}>
            <Select value={selectedAction} onChange={changeSelected}>
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
