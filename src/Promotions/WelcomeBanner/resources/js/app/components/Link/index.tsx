import './styles.scss';

type LinkProps = {
    children: any;
    href: string;
};

/**
 * @since 3.0.0
 */
export function InternalLink({children, href}: LinkProps) {
    return (
        <a href={href} className={'givewp-welcome-banner-link givewp-welcome-banner-link--internal'}>
            {children}
        </a>
    );
}

/**
 * @since 3.0.0
 */
export function ExternalLink({children, href}: LinkProps) {
    return (
        <a
            href={href}
            className={'givewp-welcome-banner-link givewp-welcome-banner-link--external'}
            target={'_blank'}
            rel="noopener noreferrer"
        >
            {children}
        </a>
    );
}
