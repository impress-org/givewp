import type {HeaderProps} from '@givewp/forms/propTypes';
import cx from 'classnames';

/**
 * @since 3.5.0 add HeaderImage
 * @since 3.0.0
 */
export default function Header({ HeaderImage, Title, Description, Goal, isMultiStep }: HeaderProps) {
    const { designSettingsImageStyle, designSettingsImageUrl } = window.givewp.form.hooks.useDonationFormSettings();

    if (!designSettingsImageUrl) {
        return (
            <div className={
                cx({
                        'givewp-layouts-header__templates': !isMultiStep,
                        'givewp-layouts-header__templates-ms': isMultiStep
                    }
                )}>
                <Title />
                <Description />
                <Goal />
            </div>
        );
    }

    return (
        <div
            className={cx({
                'givewp-layouts-header__templates': !isMultiStep,
                [`givewp-layouts-header__templates--${designSettingsImageStyle}`]: !isMultiStep,
                'givewp-layouts-header__templates-ms': isMultiStep,
                [`givewp-layouts-header__templates-ms--${designSettingsImageStyle}`]: isMultiStep
            })}
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

function HeaderImageTemplates({ imagePosition, HeaderImage, Title, Description, Goal }) {
    switch (imagePosition) {
        case 'background':
            return (
                <>
                    <div className={'givewp-layouts-header__content'}>
                        <HeaderImage />
                        <Title />
                        <Description />
                    </div>
                    <div className={'givewp-layouts-header__goal'}>
                        <Goal />
                    </div>
                </>
            );
        case 'above':
            return (
                <>
                    <HeaderImage />
                    <div className={'givewp-layouts-header__content'}>
                        <Title />
                        <Description />
                        <Goal />
                    </div>
                </>
            );
        case 'center':
            return (
                <>
                    <Title />
                    <Description />
                    <HeaderImage />
                    <Goal />
                </>
            );
        default:
            return (
                <>
                    <Title />
                    <Description />
                    <Goal />
                </>
            );
    }
}
