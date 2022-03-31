import {createContext, useRef, useState} from "react";
import {__} from "@wordpress/i18n";
import {A11yDialog} from "react-a11y-dialog";

import {GiveIcon} from '@givewp/components';

import {ListTable, ListTableColumn} from './ListTable';
import Pagination from "./Pagination";
import {Filter, getInitialFilterState} from './Filters';
import useDebounce from "./hooks/useDebounce";
import {useResetPage} from "./hooks/useResetPage";
import ListTableApi from "./api";
import styles from './ListTablePage.module.scss';
import cx from "classnames";

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
    confirm: (selected: Array<string|number>) => JSX.Element|JSX.Element[]|string;
}

export const RowActionsContext = createContext({});

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
    const [modalAction, setModalAction] = useState(-1);
    const [selected, setSelected] = useState([]);
    const dialog = useRef();

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

    const openBulkActionModal = (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);
        const action = formData.get('giveListTableBulkActions');
        const actionIndex = bulkActions.findIndex((config) => action == config.value);
        if(actionIndex < 0) return;
        setModalAction(actionIndex);
        const selectNodes = document.querySelectorAll('.giveListTableSelect:checked:not(#giveListTableSelectAll)');
        if(!selectNodes.length) return;
        const selected = Array.from(selectNodes, (select, index) => parseInt(select.dataset.id));
        setSelected(selected);
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
                    <div className={cx(styles.pageActions,
                        { [styles.alignEnd]: !bulkActions }
                    )}>
                        {bulkActions &&
                            <form id={styles.bulkActionsForm} onSubmit={openBulkActionModal}>
                                <select className={styles.bulkActions} name='giveListTableBulkActions'>
                                    <option value=''>{__('Bulk Actions', 'give')}</option>
                                    {bulkActions.map(action => (
                                        <option key={action.value} value={action.value}>{action.label}</option>
                                    ))}
                                </select>
                                <button className={styles.addFormButton}>{__('Apply', 'give')}</button>
                            </form>
                        }
                        {page && setPage && showPagination()}
                    </div>
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
                    <div className={styles.pageActions}>
                        {page && setPage && showPagination()}
                    </div>
                </div>
            </article>
            <A11yDialog
                id='giveListTableModal'
                dialogRef={instance => (dialog.current = instance)}
                title={(modalAction > - 1) ? `${bulkActions[modalAction].label} - ${pluralName}` : 'Bulk Action'}
                classNames={{
                    container: styles.container,
                    overlay: styles.overlay,
                    dialog: styles.dialog,
                    closeButton: ''
                }}
            >
                {(modalAction > -1) && bulkActions[modalAction]?.confirm(selected)}
                <button className={styles.addFormButton} onClick={(event) => dialog.current?.hide()}>
                    {__('Cancel', 'give')}
                </button>
                <button className={styles.addFormButton}
                        onClick={async (event) => {
                            dialog.current?.hide();
                            await bulkActions[modalAction]?.action(selected);
                            await mutate();
                        }}
                >
                    {__('Confirm', 'give')}
                </button>
            </A11yDialog>
        </>
    );
}
