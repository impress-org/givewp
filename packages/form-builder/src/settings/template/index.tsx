import {PanelBody, PanelRow, SelectControl, ColorPalette, BaseControl} from '@wordpress/components';
import {PanelColorSettings} from '@wordpress/block-editor'
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '../../stores/form-state';
import {getWindowData} from '@givewp/form-builder/common';
import debounce from "lodash.debounce";

const {templates} = getWindowData();

const templateOptions = Object.values(templates).map(({id, name}) => ({value: id, label: name}));

const TemplateSettings = () => {
    const {
        settings: {
            templateId,
            primaryColor,
            secondaryColor,
        },
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <>
            <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
                <PanelRow>
                    <SelectControl
                        label={__('Form template', 'give')}
                        value={templateId}
                        onChange={(templateId) => dispatch(setFormSettings({templateId}))}
                        options={templateOptions}
                    />
                </PanelRow>
            </PanelBody>
            <PanelColorSettings
                title={__('Colors')}
                colorSettings={[
                    {
                        value: primaryColor,
                        onChange: debounce((primaryColor) => dispatch(setFormSettings({primaryColor})), 100 ),
                        label: __('Primary Color', 'givewp'),
                        disableCustomColors: false,
                    },
                    {
                        value: secondaryColor,
                        onChange: debounce((secondaryColor) => dispatch(setFormSettings({secondaryColor})), 100 ),
                        label: __('Secondary Color', 'givewp'),
                        disableCustomColors: false,
                    },
                ]}
            />
        </>
    );
};

export default TemplateSettings;
