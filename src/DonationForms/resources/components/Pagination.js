import PropTypes from 'prop-types';
import {useState, useEffect} from 'react';
import styles from './Pagination.module.scss';
import cx from 'classnames';
import {__, _n} from '@wordpress/i18n';

const Pagination = ({currentPage, totalPages, totalItems = -1, disabled, setPage}) => {
    const [pageInput, setPageInput] = useState(1);

    useEffect(() => {
        setPageInput(currentPage);
    }, [currentPage]);

    const nextPage = parseInt(currentPage) + 1;
    const previousPage = parseInt(currentPage) - 1;

    return (
        <nav aria-label={__('donation forms table', 'give')} className={styles.container}>
            {totalItems >= 1 &&
                <span className={styles.totalItems}>
                    {totalItems.toString() + " " + _n('form', 'forms', totalItems, 'give')}
                </span>
            }
            {1 < totalPages &&
                <>
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
                        <label htmlFor={styles.currentPage} className={styles.visuallyHidden}>
                            {__('Current Page', 'give')}
                        </label>
                        <input className={styles.navElement} id={styles.currentPage} name={'currentPageSelector'}
                               type="number" min={1} max={totalPages} value={pageInput}
                               onChange={(e) => {
                                   const cleanValue = e.target.value.replace(/[^0-9]/, '');
                                   const page = Number(cleanValue);
                                   setPageInput(cleanValue);
                                   if (totalPages >= page && page > 0) {
                                       setPage(page);
                                   }
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
                </>
            }
        </nav>
    );
};

Pagination.propTypes = {
    // Current page
    currentPage: PropTypes.number.isRequired,
    // Total number of pages
    totalPages: PropTypes.number.isRequired,
    // Total number of items
    totalItems: PropTypes.number,
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
