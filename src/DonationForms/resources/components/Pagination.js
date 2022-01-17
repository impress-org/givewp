import PropTypes from 'prop-types';
import { useState, useEffect } from 'react';
import styles from './pagination.module.css';

const {__} = wp.i18n;

const Pagination = ({currentPage, totalPages, disabled, setPage}) => {
    const [pageInput, setPageInput] = useState(1);

    useEffect(() => {
        setPageInput(currentPage);
    },[currentPage]);

    if (1 >= totalPages) {
        return null;
    }

    const nextPage = parseInt(currentPage) + 1;
    const previousPage = parseInt(currentPage) - 1;

    return (
        <div className="tablenav bottom">
            <div className="tablenav-pages">
                <div className="pagination-links">
                    {previousPage > 0 ? (
                        <>
                            <a
                                href="#"
                                className="tablenav-pages-navspan button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    if (!disabled) {
                                        setPage(1);
                                    }
                                }}
                            >
                                «
                            </a>{' '}
                            <a
                                href="#"
                                className="tablenav-pages-navspan button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    if (!disabled) {
                                        setPage(parseInt(currentPage) - 1);
                                    }
                                }}
                            >
                                ‹
                            </a>
                        </>
                    ) : (
                        <span className="tablenav-pages-navspan button disabled">‹</span>
                    )}

                    <span id="table-paging" className="paging-input">
                        <label htmlFor="current-page-selector" className="screen-reader-text">Current Page</label>
                        <input className="current-page" id={styles.currentPageSelector} name={'currentPageSelector'} type="number" min={0} max={totalPages} value={pageInput}
                               onChange={(e) => {
                                   setPageInput(e.target.value);
                               }}
                        />
                        <span className="tablenav-paging-text">
                            {' '}
                            {__('of', 'give')} <span className="total-pages">{totalPages}</span>{' '}
                        </span>
                    </span>

                    {nextPage <= totalPages ? (
                        <>
                            <a
                                href="#"
                                className="tablenav-pages-navspan button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    if (!disabled) {
                                        setPage(parseInt(currentPage) + 1);
                                    }
                                }}
                            >
                                ›
                            </a>{' '}
                            <a
                                href="#"
                                className="tablenav-pages-navspan button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    if (!disabled) {
                                        setPage(totalPages);
                                    }
                                }}
                            >
                                »
                            </a>
                        </>
                    ) : (
                        <span className="tablenav-pages-navspan button disabled">›</span>
                    )}
                </div>
            </div>
        </div>
    );
};

Pagination.propTypes = {
    // Current page
    currentPage: PropTypes.number.isRequired,
    // Total number of pages
    totalPages: PropTypes.number.isRequired,
    // Function to set the next/previous page
    setPage: PropTypes.func.isRequired,
    // Is pagination disabled
    disabled: PropTypes.bool.isRequired,
};

Pagination.defaultProps = {
    currentPage: 1,
    totalPages: 0,
    setPage: () => {},
    disabled: false,
};

export default Pagination;
