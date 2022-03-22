import styles from './ListTablePage.module.scss';
import {ListTable, ListTableColumn} from './ListTable';
import {GiveIcon} from '@givewp/components';
import Pagination from "./Pagination";

export interface ListTablePageProps {
    headerButtons: Array<{text: string, link: string}>;
    singleName: string;
    pluralName: string;
    title: string;
    columns: Array<ListTableColumn>;
    children?: JSX.Element|JSX.Element[]|null;
    data: {items: Array<{}>, totalPages: number, totalItems: string};
    page: number;
    setPage: null|((page: number) => void);
    error?: any;
    isValidating?: Boolean;
}

export default function ListTablePage({
    headerButtons = [],
    singleName,
    pluralName,
    title,
    columns,
    children = null,
    data,
    error = false,
    isValidating = false,
    page = 1,
    setPage = null,
}: ListTablePageProps) {

    return (
        <article>
            <div className={styles.pageHeader}>
                <GiveIcon size={'1.875rem'}/>
                <h1 className={styles.pageTitle}>{title}</h1>
                {headerButtons.map(button => (
                    <a key={button.link} href={button.link} className={styles.addFormButton}>
                        {button.text}
                    </a>
                ))}
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
