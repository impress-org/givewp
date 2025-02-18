import {__, sprintf} from '@wordpress/i18n';
import cx from 'classnames';
import {ChevronLeft, ChevronRight} from './icons';

import './styles.scss';

export default ({currentPage, totalPages, setPage}: PaginationProps) => {
    if (1 >= totalPages) {
        return null;
    }

    const nextPage = currentPage + 1;
    const previousPage = currentPage - 1;

    return (
        <div className="give-campaign-components-pagination">
            <div className="give-campaign-components-pagination__pages">
                <div className="give-campaign-components-pagination__pages-links">
                    {previousPage > 0 ? (
                        <button
                            title={__('Previous page', 'give')}
                            className="give-campaign-components-pagination__pages-links-arrow"
                            onClick={(e) => {
                                e.preventDefault();
                                setPage(previousPage);
                            }}
                        >
                            <ChevronLeft />
                        </button>
                    ) : (
                        <button className="give-campaign-components-pagination__pages-links-arrow-disabled">
                            <ChevronLeft />
                        </button>
                    )}

                    {[...Array(totalPages)].map((e, i) => {
                        const page = i + 1;
                        return (
                            <button
                                title={sprintf(__('Page %d', 'give'), page)}
                                className={cx('give-campaign-components-pagination__pages-links-page', {'give-campaign-components-pagination__pages-links-current': currentPage === page})}
                                onClick={(e) => {
                                    e.preventDefault();
                                    setPage(page);
                                }}
                            >
                                {page}
                            </button>
                        )
                    })}

                    {nextPage <= totalPages ? (
                        <button
                            title={__('Next page', 'give')}
                            className="give-campaign-components-pagination__pages-links-arrow"
                            onClick={(e) => {
                                e.preventDefault();
                                setPage(nextPage);
                            }}
                        >
                            <ChevronRight />
                        </button>
                    ) : (
                        <button className="give-campaign-components-pagination__pages-links-arrow-disabled">
                            <ChevronRight />
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

interface PaginationProps {
    currentPage: number,
    totalPages: number,
    setPage: (page: number) => void
}
