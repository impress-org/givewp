import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {BaseControl} from '@wordpress/components';
import {useEffect} from 'react';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, description} = attributes;
    const intlTelInputId: string = 'give-form-builder-phone-input';

    useEffect(() => {
        const {intlTelInputSettings} = getFormBuilderWindowData();

        const css = document.createElement('link');
        css.href = intlTelInputSettings.cssUrl;
        css.rel = 'stylesheet';
        document.body.appendChild(css);

        const script = document.createElement('script');
        script.src = intlTelInputSettings.scriptUrl;
        script.async = true;
        script.onload = () => {
            // @ts-ignore
            window.intlTelInput(document.querySelector('#' + intlTelInputId), {
                showSelectedDialCode: true,
                strictMode: true,
                utilsScript: intlTelInputSettings.utilsScriptUrl,
                initialCountry: intlTelInputSettings.initialCountry,
                i18n: JSON.parse(intlTelInputSettings.i18n),
            });
        };
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(css);
            document.body.removeChild(script);
        };
    }, []);

    return (
        <div className={classnames({'give-is-required': isRequired})}>
            <BaseControl id={'give-form-builder-phone-label'} label={label} help={description}>
                <input id={intlTelInputId} type="text" />
            </BaseControl>
        </div>
    );
}
