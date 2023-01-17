import React from 'react';
import styles from './style.module.scss';

//@since 2.24.0 used to handle sort direction and column id.
const ListTableHeaders = ({column, sortField, setSortDirectionForColumn}) => {
    const handleItemSort = (event, column) => {
        event.preventDefault();
        const direction = sortField.sortDirection === 'desc' ? 'asc' : 'desc';
        setSortDirectionForColumn(column, direction);
    };
    return (
        <>
            {column.sortable ? (
                <button
                    type="button"
                    aria-label="sort"
                    className={styles['sortButton']}
                    onClick={(event) => column.sortable && handleItemSort(event, column.id)}
                >
                    <div className={styles.text}>{column.label}</div>
                    <div key={column.id} id={column.id}>
                        <svg width="16" height="7" viewBox="0 0 16 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.1699 6.5L5.66986 0.5L0.169861 6.5L11.1699 6.5Z"
                                fill={
                                    sortField.sortColumn === column.id && sortField.sortDirection === 'asc'
                                        ? '#0878b0'
                                        : '#dddddd'
                                }
                            />
                        </svg>
                        <svg width="16" height="7" viewBox="0 0 16 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.169861 0.5L5.66986 6.5L11.1699 0.5H0.169861Z"
                                fill={
                                    sortField.sortColumn === column.id && sortField.sortDirection === 'desc'
                                        ? '#0878b0'
                                        : '#dddddd'
                                }
                            />
                        </svg>
                    </div>
                </button>
            ) : (
                <div className={styles.text} id={column.id}>
                    {column.label}
                </div>
            )}
        </>
    );
};
export default ListTableHeaders;
