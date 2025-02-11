import {__} from '@wordpress/i18n';

import './styles.scss';

export default ({currentPage, totalPages, setPage, disabled = false}: PaginationProps) => {
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
                        <>
                            <a
                                href="#"
                                title={__('Previous page', 'give')}
                                onClick={(e) => {
                                    e.preventDefault();
                                    setPage(previousPage);
                                }}
                            >
                                ‹
                            </a>
                        </>
                    ) : (
                        <span className="give-campaign-components-pagination__pages-links-disabled">‹</span>
                    )}

                    <span className="give-campaign-components-pagination__pages-links-info">
                        <span>{currentPage}</span>
                        {__('of', 'give')}
                        <span>{totalPages}</span>
                    </span>

                    {nextPage <= totalPages ? (
                        <>
                            <a
                                href="#"
                                title={__('Next page', 'give')}
                                onClick={(e) => {
                                    e.preventDefault();
                                    setPage(nextPage);
                                }}
                            >
                                ›
                            </a>
                        </>
                    ) : (
                        <span className="give-campaign-components-pagination__pages-links-disabled">›</span>
                    )}
                </div>
            </div>
        </div>
    );
}

interface PaginationProps {
    currentPage: number,
    totalPages: number,
    setPage: (page: number) => void,
    disabled?: boolean
}
