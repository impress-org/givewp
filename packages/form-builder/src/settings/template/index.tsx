import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '../../stores/form-state';
import {getWindowData} from '@givewp/form-builder/common';

const {templates} = getWindowData();

const templateOptions = Object.values(templates).map(({id, name}) => ({value: id, label: name}));

const TemplateSettings = () => {
    const {
        settings: {templateId},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody>
            <PanelRow>
                <SelectControl
                    label={__('Form template', 'givewp')}
                    value={templateId}
                    onChange={(templateId) => dispatch(setFormSettings({templateId}))}
                    options={templateOptions}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default TemplateSettings;
