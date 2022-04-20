import pageStyles from "@givewp/components/ListTable/ListTablePage.module.scss";
import styles from './BulkActionSelect.module.scss';
import {__} from "@wordpress/i18n";
import Select from "@givewp/components/ListTable/Select";

export const BulkActionSelect = ({bulkActions = null, showModal, data, parameters}) => {
    if(!bulkActions){
        return null;
    }

    return (
        <form id={styles.bulkActionsForm} onSubmit={showModal}>
            <Select name='giveListTableBulkActions'>
                <option value=''>{__('Bulk Actions', 'give')}</option>
                {bulkActions.map(action => {
                    if (typeof action?.isVisible == 'function' && !action.isVisible(data, parameters)) {
                        return null;
                    }
                    return <option key={action.value} value={action.value}>{action.label}</option>;
                })}
            </Select>
            <button className={pageStyles.addFormButton}>{__('Apply', 'give')}</button>
        </form>
    );
}
