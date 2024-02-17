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
export default function Header({HeaderImage, Title, Description, Goal}) {
    const {designSettingsImageStyle, designSettingsImageUrl} = window.givewp.form.hooks.useDonationFormSettings();

    if (!designSettingsImageUrl) {
        return (
            <div className={`givewp-layouts-header__templates`}>
                <Title />
                <Description />
                <SecureBadge />
                <Goal />
            </div>
        );
    }

    return (
        <div className={`givewp-layouts-header__templates--${designSettingsImageStyle}`}>
            <HeaderImageTemplates
                designSettingsImageStyle={designSettingsImageStyle}
                HeaderImage={HeaderImage}
                Title={Title}
                Description={Description}
                Goal={Goal}
            />
        </div>
    );
}

function HeaderImageTemplates({designSettingsImageStyle, HeaderImage, Title, Description, Goal}) {
    switch (designSettingsImageStyle) {
        case 'background':
            return (
                <>
                    <HeaderImage />
                    <Title />
                    <Description />
                    <SecureBadge />
                    <Goal />
                </>
            );
        case 'above':
            return (
                <>
                    <HeaderImage />
                    <div className={'givewp-layouts givewp-layouts-headerContent'}>
                        <Title />
                        <Description />
                        <SecureBadge />
                        <Goal />
                    </div>
                </>
            );
        case 'center':
            return (
                <>
                    <Title />
                    <Description />
                    <SecureBadge />
                    <HeaderImage />
                    <Goal />
                </>
            );
        default:
            return (
                <>
                    <Title />
                    <Description />
                    <SecureBadge />
                    <Goal />
                </>
            );
    }
}
