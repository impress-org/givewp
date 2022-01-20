import PropTypes from 'prop-types';
import {useState, useEffect} from 'react';
import styles from './Pagination.module.scss';
import cx from 'classnames';

const {__} = wp.i18n;

const Pagination = ({currentPage, totalPages, disabled, setPage}) => {
    const [pageInput, setPageInput] = useState(1);

    useEffect(() => {
        setPageInput(currentPage);
    }, [currentPage]);

    if (1 >= totalPages) {
        return null;
    }

    const nextPage = parseInt(currentPage) + 1;
    const previousPage = parseInt(currentPage) - 1;

    return (
        <nav aria-label={__('donation forms table', 'give')} className={styles.container}>
            <button
                className={cx(styles.navDirection, styles.navElement)}
                disabled={previousPage <= 1}
                onClick={(e) => {
                    if (!disabled) {
                        setPage(1);
                    }
                }}
            >
                «
            </button>
            <button
                className={cx(styles.navDirection, styles.navElement)}
                disabled={previousPage <= 0}
                onClick={(e) => {
                    if (!disabled) {
                        setPage(parseInt(currentPage) - 1);
                    }
                }}
            >
                ‹
            </button>
            <span>
                <label htmlFor={styles.currentPage}
                       className={styles.visuallyHidden}>{__('Current Page', 'give')}</label>
                <input className={styles.navElement} id={styles.currentPage} name={'currentPageSelector'}
                       type="number" min={0} max={totalPages} value={pageInput}
                       onChange={(e) => {
                           setPageInput(e.target.value);
                       }}
                />
                <span>
                    {' '}
                    {__('of', 'give')} <span>{totalPages}</span>{' '}
                </span>
            </span>
            <button
                className={cx(styles.navDirection, styles.navElement)}
                disabled={nextPage > totalPages}
                onClick={(e) => {
                    if (!disabled) {
                        setPage(parseInt(currentPage) + 1);
                    }
                }}
            >
                ›
            </button>
            <button
                className={cx(styles.navDirection, styles.navElement)}
                disabled={nextPage > totalPages - 1}
                onClick={(e) => {
                    if (!disabled) {
                        setPage(totalPages);
                    }
                }}
            >
                »
            </button>
        </nav>
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
