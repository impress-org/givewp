import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {BaseControl, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import IntlTelInput from 'intl-tel-input/react';
import 'intl-tel-input/build/css/intlTelInput.css';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useEffect} from 'react';

/**
 * @since 3.9.0
 */
export default function Edit({attributes: {label, required}, setAttributes}: BlockEditProps<any>) {
    const {intlTelInputSettings} = getFormBuilderWindowData();

    useEffect(() => {
        // This timeout is necessary to fix a missing left padding that can happen in certain cases.
        const interval = setTimeout(() => {
            document.querySelectorAll('.iti__tel-input').forEach(function (input: HTMLInputElement) {
                // @ts-ignore
                const countryContainerWidth = document.querySelector('.iti__country-container').offsetWidth;
                input.style.paddingLeft = String(countryContainerWidth + 4) + 'px';
            });
        }, 100);

        return () => {
            clearInterval(interval);
        };
    }, []);

    return (
        <>
            <div className={classnames({'give-is-required': required})}>
                <BaseControl id={'give-form-builder-phone-label'} label={label}>
                    <IntlTelInput
                        initOptions={{
                            initialCountry: intlTelInputSettings.initialCountry,
                            showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                            strictMode: intlTelInputSettings.strictMode,
                            i18n: intlTelInputSettings.i18n,
                            useFullscreenPopup: intlTelInputSettings.useFullscreenPopup,
                            utilsScript: intlTelInputSettings.utilsScriptUrl,
                        }}
                    />
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
