import {createContext, useRef, useState} from "react";
import {__} from "@wordpress/i18n";
import {A11yDialog} from "react-a11y-dialog";
import A11yDialogInstance from "a11y-dialog";

import {GiveIcon} from '@givewp/components';

import {ListTable, ListTableColumn} from './ListTable';
import Pagination from "./Pagination";
import {Filter, getInitialFilterState} from './Filters';
import useDebounce from "./hooks/useDebounce";
import {useResetPage} from "./hooks/useResetPage";
import ListTableApi from "./api";
import styles from './ListTablePage.module.scss';
import cx from "classnames";
import {BulkActionSelect} from "@givewp/components/ListTable/BulkActionSelect";

export interface ListTablePageProps {
    //required
    title: string;
    columns: Array<ListTableColumn>;
    apiSettings: {apiRoot, apiNonce};

    //optional
    bulkActions?: Array<BulkActionsConfig>|null;
    pluralName?: string;
    singleName?: string;
    children?: JSX.Element|JSX.Element[]|null;
    rowActions?: JSX.Element|JSX.Element[]|Function|null;
    filterSettings?;
}

export interface BulkActionsConfig {
    //required
    label: string;
    value: string|number;
    action: (selected: Array<string|number>) => Promise<{errors: string|number, successes: string|number}>;
    confirm: (selected: Array<string|number>, names?: Array<string>) => JSX.Element|JSX.Element[]|string;

    //optional
    isVisible?: (data, parameters) => Boolean;
}

export const ShowConfirmModalContext = createContext((label, confirm, action) => {});

export default function ListTablePage({
    title,
    columns,
    apiSettings,
    bulkActions = null,
    filterSettings = [],
    singleName = __('item', 'give'),
    pluralName  = __('items', 'give'),
    rowActions = null,
    children = null,
}: ListTablePageProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [filters, setFilters] = useState(getInitialFilterState(filterSettings));
    const [modalContent, setModalContent] = useState<{confirm, action, label}>({
        confirm: (selected)=>{},
        action: (selected)=>{},
        label: ''
    });
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectedNames, setSelectedNames] = useState([]);
    const dialog = useRef() as {current: A11yDialogInstance};

    const parameters = {
        page,
        perPage,
        ...filters
    };

    const archiveApi = useRef(new ListTableApi(apiSettings)).current;

    const {data, error, isValidating, mutate} = archiveApi.useListTable(parameters)

    useResetPage(data, page, setPage, filters);

    const handleFilterChange = (name, value) => {
        setFilters(prevState => ({...prevState, [name]: value}));
    }

    const handleDebouncedFilterChange = useDebounce(handleFilterChange);

    const showConfirmActionModal = (label, confirm, action) => {
        setModalContent({confirm, action, label});
        dialog.current.show();
    }

    const openBulkActionModal = (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);
        const action = formData.get('giveListTableBulkActions');
        const actionIndex = bulkActions.findIndex((config) => action == config.value);
        if(actionIndex < 0) return;
        const selectNodes = document.querySelectorAll('.giveListTableSelect:checked:not(#giveListTableSelectAll)');
        if(!selectNodes.length) return;
        const selected = Array.from(selectNodes, (select:HTMLSelectElement) => parseInt(select.dataset.id));
        const names = Array.from(selectNodes, (select:HTMLSelectElement) => select.dataset.name);
        setModalContent({...bulkActions[actionIndex]});
        setSelectedIds(selected);
        setSelectedNames(names);
        dialog.current.show();
    }

    const showPagination = () => (
        <Pagination
            currentPage={page}
            totalPages={data ? data.totalPages : 1}
            disabled={!data}
            totalItems={data ? parseInt(data.totalItems) : -1}
            setPage={setPage}
        />
    )

    const PageActions = () => (
        <div className={cx(styles.pageActions,
            { [styles.alignEnd]: !bulkActions }
        )}>
            <BulkActionSelect parameters={parameters} data={data} bulkActions={bulkActions} showModal={openBulkActionModal}/>
            {page && setPage && showPagination()}
        </div>
    );

    return (
        <>
            <article className={styles.page}>
                <header className={styles.pageHeader}>
                    <div className={styles.pageTitleContainer}>
                        <GiveIcon size={'1.875rem'}/>
                        <h1 className={styles.pageTitle}>{title}</h1>
                    </div>
                    {children}
                </header>
                <section role='search' className={styles.searchContainer}>
                    {filterSettings.map(filter =>
                        <Filter key={filter.name} filter={filter} onChange={handleFilterChange} debouncedOnChange={handleDebouncedFilterChange}/>
                    )}
                </section>
                <div className={cx('wp-header-end', 'hidden')}/>
                <div className={styles.pageContent}>
                    <PageActions/>
                    <ShowConfirmModalContext.Provider value={showConfirmActionModal}>
                        <ListTable
                            columns={columns}
                            singleName={singleName}
                            pluralName={pluralName}
                            title={title}
                            rowActions={rowActions}
                            parameters={parameters}
                            data={data}
                            error={error}
                            isLoading={isValidating}
                        />
                    </ShowConfirmModalContext.Provider>
                    <PageActions/>
                </div>
            </article>
            <A11yDialog
                id='giveListTableModal'
                dialogRef={instance => (dialog.current = instance)}
                title={`${modalContent.label} - ${title}`}
                classNames={{
                    container: styles.container,
                    overlay: styles.overlay,
                    dialog: styles.dialog,
                    closeButton: ''
                }}
            >
                {modalContent?.confirm(selectedIds, selectedNames) || null}
                <button className={styles.addFormButton} onClick={(event) => dialog.current?.hide()}>
                    {__('Cancel', 'give')}
                </button>
                <button className={styles.addFormButton}
                        onClick={async (event) => {
                            dialog.current?.hide();
                            await modalContent.action(selectedIds);
                            await mutate();
                        }}
                >
                    {__('Confirm', 'give')}
                </button>
            </A11yDialog>
        </>
    );
}
