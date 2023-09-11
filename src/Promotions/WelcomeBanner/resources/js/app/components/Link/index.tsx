import './styles.scss';

type LinkProps = {
    children: any;
    href: string;
};

/**
 * @unreleased
 */
export function InternalLink({children, href}: LinkProps) {
    return (
        <a href={href} className={'givewp-welcome-banner-link givewp-welcome-banner-link--internal'}>
            {children}
        </a>
    );
}

/**
 * @unreleased
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
