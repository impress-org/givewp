import type {ReactNode} from 'react';
import {useId} from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import {Button, ExternalLink} from '@wordpress/components';
import {close} from '@wordpress/icons';
import './styles.scss';

/**
 * @since TBD
 */
export interface BalloonProps {
    /** The bold first line. A balloon with a title is a labeled region. */
    title?: string;
    /** The message. */
    children: ReactNode;
    /** An optional "read more" link under the message. */
    link?: {href: string; text: string};
    /** Shows the close button in the top end corner. */
    onDismiss?: () => void;
    /** Text buttons under the message, like "Got it". */
    actions?: ReactNode;
    /** The placement class of the screen that shows it. The balloon itself sets no position. */
    className?: string;
}

/**
 * A floating onboarding tip in the stock WordPress look: a white box, a 1px neutral edge and a small
 * shadow. The screen places it with `className`.
 *
 * @since TBD
 */
export default function Balloon({title, children, link, onDismiss, actions, className}: BalloonProps) {
    const titleId = useId();
    const Root = title ? 'section' : 'div';

    return (
        <Root className={cx('givewp-balloon', className)} aria-labelledby={title ? titleId : undefined}>
            {onDismiss && (
                <Button
                    className="givewp-balloon__close"
                    icon={close}
                    iconSize={20}
                    isSmall
                    label={__('Dismiss', 'give')}
                    showTooltip={false}
                    onClick={() => onDismiss()}
                />
            )}
            {title && (
                <h3 id={titleId} className="givewp-balloon__title">
                    {title}
                </h3>
            )}
            <div className="givewp-balloon__body">{children}</div>
            {link && (
                <p className="givewp-balloon__link">
                    <ExternalLink href={link.href}>{link.text}</ExternalLink>
                </p>
            )}
            {actions && <div className="givewp-balloon__actions">{actions}</div>}
        </Root>
    );
}
