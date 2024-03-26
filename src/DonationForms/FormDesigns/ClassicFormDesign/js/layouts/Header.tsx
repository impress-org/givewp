import {__} from '@wordpress/i18n';
import {HeaderProps} from '@givewp/forms/propTypes';

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
 * @since 3.5.0 add HeaderImage
 * @since 3.0.0
 */
export default function Header({HeaderImage, Title, Description, Goal}: HeaderProps) {
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
        <div
            className={`givewp-layouts-header__templates givewp-layouts-header__templates--${designSettingsImageStyle}`}
        >
            <HeaderImageTemplates
                imagePosition={designSettingsImageStyle}
                HeaderImage={HeaderImage}
                Title={Title}
                Description={Description}
                Goal={Goal}
            />
        </div>
    );
}

function HeaderImageTemplates({imagePosition, HeaderImage, Title, Description, Goal}) {
    switch (imagePosition) {
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
                    <div className={'givewp-layouts-header__content'}>
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
