import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useEffect} from 'react';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, description, placeholder, defaultValue} = attributes;
    const inputId: string = 'give-form-builder-phone-input';

    useEffect(() => {
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
                utilsScript: IntlTelInput.utilsScriptUrl,
                initialCountry: 'us',
                i18n: JSON.parse(IntlTelInput.i18n),
            });
        }, 100);

        return () => {
            clearInterval(interval);
        };
    }, []);

    return (
        <div className={classnames({'give-is-required': isRequired})}>
            <span className="components-input-control__label give-text-block__label">{label}</span>
            {description && <p className="give-text-block__description">{description}</p>}
            <input id={inputId} type="text" />
        </div>
    );
}
