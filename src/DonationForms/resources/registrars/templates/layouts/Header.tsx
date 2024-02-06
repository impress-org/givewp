import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @unreleased add background style wrapper
 * @since 3.0.0
 */
export default function Header({HeaderImage, Title, Description, Goal}: HeaderProps) {
    const {designSettingsImageStyle} = window.givewp.form.hooks.useDonationFormSettings();

    if (designSettingsImageStyle === 'background') {
        return (
            <>
                <div className={'givewp-layouts givewp-design-settings-image--background'}>
                    <Title />
                    <Description />
                </div>
                <Goal />
            </>
        );
    }

    return (
        <>
            <Title />
            <Description />
            <HeaderImage />
            <Goal />
        </>
    );
}
