import {Children, type ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import Notice from '@givewp/admin/components/Notices';
import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import type {ListTableNotice} from '../notices/types';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableNoticeAreaProps {
    notices: ListTableNotice[];
    /** Today's recommendation, or null when there is none. */
    recommendation: RecommendedProductData | null;
    /** Leave it out to show the recommendation without a dismiss button. */
    onDismissRecommendation?: () => void;
    /** Rendered first: the error notices of failed actions. */
    children?: ReactNode;
    /** Rendered last: the deprecated `productRecommendation` slot. */
    trailing?: ReactNode;
}

/**
 * @since TBD
 */
function NoticeLink({href, label, opensInNewTab}: {href: string; label: string; opensInNewTab?: boolean}) {
    return (
        <a
            className="givewp-list-table-notice-area__link"
            href={href}
            {...(opensInNewTab ? {target: '_blank', rel: 'noopener noreferrer'} : {})}
        >
            {label}
            {opensInNewTab && (
                <>
                    {' '}
                    <span className="screen-reader-text">{__('(opens in a new tab)', 'give')}</span>
                </>
            )}
        </a>
    );
}

/**
 * The one place above a list table where notices, onboarding tips and the daily product
 * recommendation render. Everything comes from page config, so there is no loading state. When
 * there is nothing to show, the wrapper is hidden by the `:empty` rule in the stylesheet, so it takes
 * no space. (It returns null when there is also no `children` at all; the page always passes the
 * action notification placeholder, so there the wrapper is what gets hidden.)
 *
 * Order: `children`, system notices, onboarding notices, the recommendation, then `trailing`.
 *
 * @since TBD
 */
export default function ListTableNoticeArea({
    notices,
    recommendation,
    onDismissRecommendation,
    children,
    trailing,
}: ListTableNoticeAreaProps) {
    const ordered = [
        ...notices.filter((notice) => notice.kind === 'system'),
        ...notices.filter((notice) => notice.kind === 'onboarding'),
    ];

    if (ordered.length === 0 && !recommendation && Children.toArray(children).length === 0 && !trailing) {
        return null;
    }

    return (
        <div className="givewp-list-table-notice-area">
            {children}

            {ordered.map(({id, title, message, link, onDismiss}) => (
                <Notice key={id} type="info" title={title} dismissHandleClick={onDismiss}>
                    <p className="givewp-list-table-notice-area__message">
                        {message}
                        {link && (
                            <>
                                {' '}
                                <NoticeLink {...link} />
                            </>
                        )}
                    </p>
                </Notice>
            ))}

            {recommendation && (
                <Notice
                    type="info"
                    ariaLabel={__('Pro tip', 'give')}
                    dismissLabel={__('Dismiss tip', 'give')}
                    dismissHandleClick={onDismissRecommendation}
                >
                    <p className="givewp-list-table-notice-area__message">
                        <strong>{__('PRO TIP: ', 'give')}</strong>
                        {recommendation.message}{' '}
                        <NoticeLink href={recommendation.documentationPage} label={recommendation.innerHtml} opensInNewTab />
                    </p>
                </Notice>
            )}

            {trailing}
        </div>
    );
}
