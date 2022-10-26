import React from 'react';
import styles from './style.module.scss';

//@unreleased component used to handle sort direction and column.
const TableSort = ({column, sortingInfo, setSortDirectionForColumn}) => {
    //Do not release : FE Testing purposes only. -----
    const shouldDisplaySort = (column) => {
        const willNotDisplay = ['amount', 'paymentType', 'donationRevenue'];
        const check = (name) => name === column.name;
        return willNotDisplay.some(check);
    };
    column.isSortable = !shouldDisplaySort(column);
    // End Test: -----

    const {sortColumn, sortDirection} = sortingInfo;
    return (
        <>
            {!column.isSortable ? null : (
                <div className={styles.container}>
                    <svg
                        onClick={() => setSortDirectionForColumn(column.name, 'asc')}
                        width="16"
                        height="7"
                        viewBox="0 0 16 7"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M11.1699 6.5L5.66986 0.5L0.169861 6.5L11.1699 6.5Z"
                            fill={sortColumn === column.name && sortDirection === 'asc' ? '#0878b0' : '#dddddd'}
                        />
                    </svg>
                    <svg
                        onClick={() => setSortDirectionForColumn(column.name, 'desc')}
                        width="16"
                        height="7"
                        viewBox="0 0 16 7"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M0.169861 0.5L5.66986 6.5L11.1699 0.5H0.169861Z"
                            fill={sortColumn === column.name && sortDirection === 'desc' ? '#0878b0' : '#dddddd'}
                        />
                    </svg>
                </div>
            )}
        </>
    );
};
export default TableSort;
