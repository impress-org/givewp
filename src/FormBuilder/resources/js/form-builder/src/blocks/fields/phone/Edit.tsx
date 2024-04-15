import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {BaseControl, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {useEffect} from 'react';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';

/**
 * @unreleased
 */
export default function Edit({attributes: {label, required}, setAttributes}: BlockEditProps<any>) {
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
                showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                strictMode: intlTelInputSettings.strictMode,
                utilsScript: intlTelInputSettings.utilsScriptUrl,
                initialCountry: intlTelInputSettings.initialCountry,
                i18n: intlTelInputSettings.i18n,
            });
        };
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(css);
            document.body.removeChild(script);
        };
    }, []);

    return (
        <>
            <div className={classnames({'give-is-required': required})}>
                <BaseControl id={'give-form-builder-phone-label'} label={label}>
                    <input id={intlTelInputId} type="text" />
                </BaseControl>
            </div>

            <InspectorControls>
                <PanelBody title={__('Field Settings', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label')}
                            value={label}
                            onChange={(value) => setAttributes({label: value})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Required', 'give')}
                            checked={required}
                            onChange={(value) => setAttributes({required: value})}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
