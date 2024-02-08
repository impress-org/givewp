import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Header({HeaderImage, Title, Description, Goal}: HeaderProps) {
    const {designSettingsImageStyle} = window.givewp.form.hooks.useDonationFormSettings();

    if (designSettingsImageStyle === 'background') {
        return (
            <>
                <div className={'givewp-multistep-header-background'}>
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
