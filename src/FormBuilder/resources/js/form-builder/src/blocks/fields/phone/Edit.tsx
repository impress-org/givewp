import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useEffect} from 'react';

const script = document.createElement('script');
script.src = 'https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/js/intlTelInput.min.js';
script.async = true;
document.body.appendChild(script);

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, description, placeholder, defaultValue} = attributes;

    const {i18nIntlTelInput} = getFormBuilderWindowData();

    console.log('input: ', i18nIntlTelInput);

    useEffect(() => {
        const input = document.querySelector('#phone');
        console.log('input: ', input);

        // @ts-ignore
        window.intlTelInput(input, {
            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/js/utils.js',
            initialCountry: 'us',
            //showSelectedDialCode: true,
            //strictMode: true,
            i18n: {...i18nIntlTelInput},
        });
    }, []);

    return (
        <div className={classnames({'give-is-required': isRequired})}>
            <link
                rel="stylesheet"
                href="https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/css/intlTelInput.css"
            />
            <span className="components-input-control__label give-text-block__label">{label}</span>
            {/*description && <p className="give-text-block__description">{description}</p>*/}
            <input id={'phone'} type="text" />
        </div>
    );
}
