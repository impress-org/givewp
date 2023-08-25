import './styles.scss';

type LinkProps = {
    children: any;
    href: string;
};

export function InternalLink({children, href}: LinkProps) {
    return (
        <a href={href} className={'givewp-welcome-banner-link givewp-welcome-banner-link--internal'}>
            {children}
        </a>
    );
}

export function ExternalLink({children, href}: LinkProps) {
    return (
        <a href={href} className={'givewp-welcome-banner-link givewp-welcome-banner-link--external'}>
            {children}
        </a>
    );
}
