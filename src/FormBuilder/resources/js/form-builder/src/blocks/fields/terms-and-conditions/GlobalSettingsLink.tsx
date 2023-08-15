import {__} from '@wordpress/i18n';

type GlobalSettingsLinkProps = {
    href: string;
};

export default function GlobalSettingsLink({href}: GlobalSettingsLinkProps) {
    return (
        <p
            style={{
                color: '#595959',
                fontStyle: 'SF Pro Text',
                fontSize: '0.75rem',
                lineHeight: '140%',
                fontWeight: 400,
            }}
        >
            {__(' Go to the settings to change the ')}
            <a href={href}>{__('global options.')}</a>
        </p>
    );
}
