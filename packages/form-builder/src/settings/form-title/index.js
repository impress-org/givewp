import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

const FormTitleSettings = () => {
    const {
        settings: {formTitle},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody>
            <PanelRow>
                <TextControl
                    label={__('Form Title')}
                    value={formTitle}
                    onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default FormTitleSettings;
