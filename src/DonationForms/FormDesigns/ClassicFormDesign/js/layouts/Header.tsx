import {__} from '@wordpress/i18n';

/**
 * @since 3.0.0
 */
const SecureBadge = () => {
    return (
        <aside className="givewp-form-secure-badge">
            <i className="fa-solid fa-lock givewp-secondary-color"></i>
            <span>{__('100% Secure Donation', 'give')}</span>
        </aside>
    );
};

/**
 * @since 3.0.0
 */
export default function Header({Title, Description, Goal}) {
    return (
        <>
            <Title />
            <Description />
            <SecureBadge />
            <Goal />
        </>
    );
}


