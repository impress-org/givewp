import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';
import {BaseControl, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {useEffect, useRef} from 'react';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {__} from '@wordpress/i18n';
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';
import {InspectorControls} from '@wordpress/block-editor';

/**
 * @unreleased
 */
export default function Edit({attributes: {label, required}, setAttributes}: BlockEditProps<any>) {
    const intlTelInputRef = useRef(null);

    useEffect(() => {
        const {intlTelInputSettings} = getFormBuilderWindowData();

        const interval = setTimeout(
            () => {
                intlTelInput(intlTelInputRef.current, {
                    utilsScript: intlTelInputSettings.utilsScriptUrl,
                    initialCountry: intlTelInputSettings.initialCountry,
                    showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                    strictMode: intlTelInputSettings.strictMode,
                    i18n: intlTelInputSettings.i18n,
                });
            },
            100 // It's necessary to properly load the utilsScript in the form builder
        );

        return () => {
            clearInterval(interval);
        };
    }, []);

    return (
        <>
            <div className={classnames({'give-is-required': required})}>
                <BaseControl id={'give-form-builder-phone-label'} label={label}>
                    <input ref={intlTelInputRef} type="text" />
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
