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
    align?: 'start'|'center'|'end';
}

export interface FilterConfig {
    // required
    name: string;
    type: 'select'|'formselect'|'search';

    // optional
    ariaLabel?: string;
    inlineSize?: string;
    text?: string;
    options?: Array<{text:string, value:string}>
}

export interface BulkActionsConfig {
    //required
    label: string;
    value: string|number;
    action: (selected: Array<string|number>) => Promise<{errors: string|number, successes: string|number}>;
    confirm: (selected: Array<string|number>, names?: Array<string>) => JSX.Element|JSX.Element[]|string;

    //optional
    isVisible?: (data, parameters) => Boolean;
    type?: 'normal'|'warning'|'danger';
}

export const ShowConfirmModalContext = createContext((label, confirm, action, type=null) => {});
export const CheckboxContext = createContext(null);

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
    align = 'start',
}: ListTablePageProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(30);
    const [filters, setFilters] = useState(getInitialFilterState(filterSettings));
    const [modalContent, setModalContent] = useState<{confirm, action, label, type?: 'normal'|'warning'|'danger'}>({
        confirm: (selected)=>{},
        action: (selected)=>{},
        label: ''
    });
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectedNames, setSelectedNames] = useState([]);
    const dialog = useRef() as {current: A11yDialogInstance};
    const checkboxRefs = useRef([]);

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

    const showConfirmActionModal = (label, confirm, action, type:'normal'|'warning'|'danger'|null = null) => {
        setModalContent({confirm, action, label, type});
        dialog.current.show();
    }

    const openBulkActionModal = (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);
        const action = formData.get('giveListTableBulkActions');
        const actionIndex = bulkActions.findIndex((config) => action == config.value);
        if(actionIndex < 0) return;
        const selected = [];
        const names = [];
        checkboxRefs.current.forEach((checkbox) => {
            if(checkbox.checked){
                selected.push(checkbox.dataset.id);
                names.push(checkbox.dataset.name);
            }
        });
        setSelectedIds(selected);
        setSelectedNames(names);
        if(selected.length){
            setModalContent({...bulkActions[actionIndex]});
            dialog.current.show();
        }
    }

    const showPagination = () => (
        <Pagination
            currentPage={page}
            totalPages={data ? data.totalPages : 1}
            disabled={!data}
            totalItems={data ? parseInt(data.totalItems) : -1}
            setPage={setPage}
            singleName={singleName}
            pluralName={pluralName}
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
                    <div className={styles.flexRow}>
                        <GiveIcon size={'1.875rem'}/>
                        <h1 className={styles.pageTitle}>{title}</h1>
                    </div>
                    {children &&
                        <div className={styles.flexRow}>
                            {children}
                        </div>
                    }
                </header>
                <section role='search' id={styles.searchContainer}>
                    {filterSettings.map(filter =>
                        <Filter
                            key={filter.name}
                            value={filters[filter.name]}
                            filter={filter}
                            onChange={handleFilterChange}
                            debouncedOnChange={handleDebouncedFilterChange}
                        />
                    )}
                </section>
                <div className={cx('wp-header-end', 'hidden')}/>
                <div className={styles.pageContent}>
                    <PageActions/>
                    <CheckboxContext.Provider value={checkboxRefs}>
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
                                align={align}
                            />
                        </ShowConfirmModalContext.Provider>
                    </CheckboxContext.Provider>
                    <PageActions/>
                </div>
            </article>
            <A11yDialog
                id='giveListTableModal'
                dialogRef={instance => (dialog.current = instance)}
                title={modalContent.label}
                titleId={styles.modalTitle}
                classNames={{
                    container: styles.container,
                    overlay: styles.overlay,
                    dialog: cx(styles.dialog, {
                        [styles.warning]: modalContent?.type === 'warning',
                        [styles.danger]: modalContent?.type === 'danger',
                    }),
                    closeButton: 'hidden',
                }}
            >
                <div className={styles.modalContent}>
                    {modalContent?.confirm(selectedIds, selectedNames) || null}
                </div>
                <div className={styles.gutter}>
                    <button id={styles.cancel} onClick={(event) => dialog.current?.hide()}>
                        {__('Cancel', 'give')}
                    </button>
                    <button id={styles.confirm}
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
