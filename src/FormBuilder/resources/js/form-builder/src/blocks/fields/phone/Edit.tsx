import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {BaseControl} from '@wordpress/components';

export default function Edit({attributes}: BlockEditProps<any>) {
    //console.log('attributes: ', attributes);
    const {label, isRequired, description, fieldName, placeholder, defaultValue} = attributes;
    //const inputId: string = 'give-form-builder-phone-input';

    /*useEffect(() => {
        const {IntlTelInput} = getFormBuilderWindowData();

        const css = document.createElement('link');
        css.href = IntlTelInput.cssUrl;
        css.rel = 'stylesheet';
        document.body.appendChild(css);

        const script = document.createElement('script');
        script.src = IntlTelInput.scriptUrl;
        script.async = true;
        document.body.appendChild(script);

        const interval = setTimeout(() => {
            // @ts-ignore
            window.intlTelInput(document.querySelector('#' + inputId), {
                showSelectedDialCode: true,
                strictMode: true,
                utilsScript: IntlTelInput.utilsScriptUrl,
                initialCountry: IntlTelInput.initialCountry,
                i18n: JSON.parse(IntlTelInput.i18n),
            });
        }, 100);

        return () => {
            clearInterval(interval);
        };
    }, []);*/

    return (
        <div className={classnames({'give-is-required': isRequired})}>
            <BaseControl id={'give-form-builder-phone-label'} label={label} help={description}>
                <input id={fieldName} name={fieldName} type="text" />
            </BaseControl>
        </div>
    );
}
