import Select from '@givewp/components/ListTable/Select';
import { FilterContainer } from '@givewp/components/ListTable/Filters';
import {__} from '@wordpress/i18n';
import styles from './BulkActionSelect.module.scss';

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
        <div id={styles.bulkActionsForm}>
            <FilterContainer id={'bulk-actions'} useArrow={true}>
                <Select className={styles.bulkSelect} value={selectedAction} onChange={changeSelected}>
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
            </FilterContainer>
            <button onClick={showModal} className={styles.apply}>
                {__('Apply', 'give')}
            </button>
        </div>
    );
};
