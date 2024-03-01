import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {ListTable} from '@givewp/components';
import A11yDialogInstance from 'a11y-dialog';
import ListTableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {createContext, useRef, useState} from 'react';
import ListTableApi from '@givewp/components/ListTable/api';
import {useResetPage} from '@givewp/components/ListTable/hooks/useResetPage';
import {A11yDialog} from 'react-a11y-dialog';
import styles from './InnerPageListTable.module.scss';

export const ShowConfirmModalContext = createContext((label, confirm, action, type = null) => {});

export default function InnerPageListTable({apiSettings, listTableBlankSlate, rowActions, singleName, pluralName, title}) {
    const [page, setPage] = useState<number>(1);
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectedNames, setSelectedNames] = useState([]);
    const [modalContent, setModalContent] = useState<{
        confirm;
        action;
        label;
        type?: 'normal' | 'warning' | 'danger';
    }>({
        confirm: (selected) => {},
        action: (selected) => {},
        label: '',
    });
    const dialog = useRef() as {current: A11yDialogInstance};
    const [sortField, setSortField] = useState<{sortColumn: string; sortDirection: string}>({
        sortColumn: 'id',
        sortDirection: 'asc',
    });
    const {sortColumn, sortDirection} = sortField;

    const parameters = {
        page,
        perPage: 30,
        sortColumn,
        sortDirection,
        locale: navigator.language,
        testMode: undefined,
    };

    const archiveApi = useRef(new ListTableApi(apiSettings)).current;

    const {data, error, isValidating, mutate} = archiveApi.useListTable(parameters);

    useResetPage(data, page, setPage, {});

    const showConfirmActionModal = (label, confirm, action, type: 'normal' | 'warning' | 'danger' | null = null) => {
        setModalContent({confirm, action, label, type});
        dialog.current.show();
    };

    const setSortDirectionForColumn = (column, direction) => {
        setSortField((previousState) => {
            return {
                ...previousState,
                sortColumn: column,
                sortDirection: direction,
            };
        });
    };

    return (
        <>
            <ShowConfirmModalContext.Provider value={showConfirmActionModal}>
                <div className={styles.innerPageListTable}>
                <ListTable
                    apiSettings={apiSettings}
                    sortField={sortField}
                    setSortDirectionForColumn={setSortDirectionForColumn}
                    singleName={singleName}
                    pluralName={pluralName}
                    title={title}
                    rowActions={rowActions}
                    parameters={{}}
                    data={data}
                    error={error}
                    isLoading={isValidating}
                    align={'start'}
                    testMode={undefined}
                    listTableBlankSlate={listTableBlankSlate()}
                    columnFilters={[]}
                />
                </div>
            </ShowConfirmModalContext.Provider>

            <A11yDialog
                id="giveListTableModal"
                dialogRef={(instance) => (dialog.current = instance)}
                title={modalContent.label}
                titleId={ListTableStyles.modalTitle}
                classNames={{
                    container: ListTableStyles.container,
                    overlay: ListTableStyles.overlay,
                    dialog: cx(ListTableStyles.dialog, {
                        [ListTableStyles.warning]: modalContent?.type === 'warning',
                        [ListTableStyles.danger]: modalContent?.type === 'danger',
                    }),
                    closeButton: 'hidden',
                }}
            >
                <div className={ListTableStyles.modalContent}>
                    {modalContent?.confirm(selectedIds, selectedNames) || null}
                </div>
                <div className={ListTableStyles.gutter}>
                    <button id={ListTableStyles.cancel} onClick={(event) => dialog.current?.hide()}>
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        id={ListTableStyles.confirm}
                        onClick={async (event) => {
                            dialog.current?.hide();
                            await modalContent.action(selectedIds);
                            await mutate();
                        }}
                    >
                        {__('Confirm', 'give')}
                    </button>
                </div>
            </A11yDialog>
        </>
    );
}
