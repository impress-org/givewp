import {__} from '@wordpress/i18n';
import CustomFilter from '../CustomFilter';
import styles from './BulkActionSelect.module.scss';
import pageStyles from '../ListTablePage/ListTablePage.module.scss';

export const BulkActionSelect = ({bulkActions = null, selectedState, showModal, data, parameters}) => {
    const [selectedAction, setSelectedAction] = selectedState;

    if (window.GiveDonations && window.GiveDonations.addonsBulkActions) {
        bulkActions = [...bulkActions, ...window.GiveDonations.addonsBulkActions];
    }

    if (!bulkActions) {
        return null;
    }

    // Format bulkActions for Custom Select
    const filteredOptions = bulkActions
        .filter(action => {
            if (typeof action?.isVisible == 'function' && !action.isVisible(data, parameters)) {
                return false;
            }
            return true;
        })
        .map(action => ({
            value: action.value,
            text: action.label
        }));

    const changeSelected = (name, value) => {
        setSelectedAction(value);
    };

    return (
        <div id={styles.bulkActionsForm}>
            <CustomFilter
                name="bulkActions"
                options={filteredOptions}
                ariaLabel={__('Bulk Actions', 'give')}
                placeholder={__('Bulk Actions', 'give')}
                onChange={changeSelected}
                value={selectedAction}
                isSearchable={false}
                isSelectable={true}
                isClearable={true}
            />
            <button
                onClick={showModal}
                className={`button button-tertiary ${pageStyles.secondaryActionButton}`}
                disabled={!selectedAction}
            >
                {__('Apply', 'give')}
            </button>
        </div>
    );
};
