import styles from './ListTablePage.module.scss';
import {ListTable, ListTableColumn} from './ListTable';
import {GiveIcon} from '@givewp/components';
import Pagination from "./Pagination";

export interface ListTablePageProps {
    //required
    singleName: string;
    pluralName: string;
    title: string;
    columns: Array<ListTableColumn>;
    data: {items: Array<{}>, totalPages: number, totalItems: string};

    //optional
    inHeader?: JSX.Element|JSX.Element[]|null;
    children?: JSX.Element|JSX.Element[]|null;
    rowActions: JSX.Element|JSX.Element[]|null;
    page?: number;
    setPage?: null|((page: number) => void);
    error?: any;
    isValidating?: Boolean;
}

export default function ListTablePage({
    singleName,
    pluralName,
    title,
    columns,
    data,
    rowActions = null,
    children = null,
    inHeader = null,
    error = false,
    isValidating = false,
    page = 0,
    setPage = null,
}: ListTablePageProps) {

    return (
        <article>
            <div className={styles.pageHeader}>
                <GiveIcon size={'1.875rem'}/>
                <h1 className={styles.pageTitle}>{title}</h1>
                {inHeader}
            </div>
            <div className={styles.searchContainer}>
                {children}
            </div>
            <div className={styles.pageContent}>
                <div className={styles.pageActions}>
                    {page && setPage &&
                        <Pagination
                            currentPage={page}
                            totalPages={data ? data.totalPages : 1}
                            disabled={!data}
                            totalItems={data ? parseInt(data.totalItems) : -1}
                            setPage={setPage}
                        />
                    }
                </div>
                    <ListTable
                        columns={columns}
                        singleName={singleName}
                        pluralName={pluralName}
                        title={title}
                        rowActions={rowActions}
                        data={data}
                        error={error}
                        isValidating={isValidating}
                    />
                <div className={styles.pageActions}>
                    {page && setPage &&
                        <Pagination
                            currentPage={page}
                            totalPages={data ? data.totalPages : 1}
                            disabled={!data}
                            totalItems={data ? parseInt(data.totalItems) : -1}
                            setPage={setPage}
                        />
                    }
                </div>
            </div>
        </article>
    );
}
