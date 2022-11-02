import React from 'react';
import styles from './style.module.scss';

//@unreleased component used to handle sort direction and column.
const TableSort = ({column, sortData, handleItemSort}) => {
    return (
        <>
            {column.isSortable ? (
                <button
                    type="button"
                    aria-label="sort"
                    onClick={(event) => column.isSortable && handleItemSort(event, column.name)}
                >
                    <div className={styles.text}>{column.text}</div>
                    <div key={column.name} id={column.name}>
                        <svg width="16" height="7" viewBox="0 0 16 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.1699 6.5L5.66986 0.5L0.169861 6.5L11.1699 6.5Z"
                                fill={
                                    sortData.sortColumn === column.name && sortData.sortDirection === 'asc'
                                        ? '#0878b0'
                                        : '#dddddd'
                                }
                            />
                        </svg>
                        <svg width="16" height="7" viewBox="0 0 16 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.169861 0.5L5.66986 6.5L11.1699 0.5H0.169861Z"
                                fill={
                                    sortData.sortColumn === column.name && sortData.sortDirection === 'desc'
                                        ? '#0878b0'
                                        : '#dddddd'
                                }
                            />
                        </svg>
                    </div>
                </button>
            ) : (
                <div className={styles.text}>{column.text}</div>
            )}
        </>
    );
};
export default TableSort;
