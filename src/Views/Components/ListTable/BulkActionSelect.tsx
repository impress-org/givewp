import styles from "@givewp/components/ListTable/ListTablePage.module.scss";
import {__} from "@wordpress/i18n";

export function BulkActionSelect ({bulkActions = null, showModal, data}) {
    if(!bulkActions){
        return null;
    }

    return (
        <form id={styles.bulkActionsForm} onSubmit={showModal}>
            <select className={styles.bulkActions} name='giveListTableBulkActions'>
                <option value=''>{__('Bulk Actions', 'give')}</option>
                {bulkActions.map(action => {
                    if (typeof action?.isVisible == 'function' && !action.isVisible(data)) {
                        return null;
                    }
                    return <option key={action.value} value={action.value}>{action.label}</option>;
                })}
            </select>
            <button className={styles.addFormButton}>{__('Apply', 'give')}</button>
        </form>
    );
}

