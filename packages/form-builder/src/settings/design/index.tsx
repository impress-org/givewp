import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {PanelColorSettings} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '../../stores/form-state';
import {getWindowData} from '@givewp/form-builder/common';
import debounce from 'lodash.debounce';

const {formDesigns} = getWindowData();

const designOptions = Object.values(formDesigns).map(({id, name}) => ({value: id, label: name}));

const FormDesignSettings = () => {
    const {
        settings: {designId, primaryColor, secondaryColor},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <>
            <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
                <PanelRow>
                    <SelectControl
                        label={__('Form design', 'give')}
                        value={designId}
                        onChange={(designId) => dispatch(setFormSettings({designId}))}
                        options={designOptions}
                    />
                </PanelRow>
            </PanelBody>
            <PanelColorSettings
                title={__('Colors')}
                colorSettings={[
                    {
                        value: primaryColor,
                        onChange: debounce((primaryColor) => dispatch(setFormSettings({primaryColor})), 100),
                        label: __('Primary Color', 'givewp'),
                        disableCustomColors: false,
                    },
                    {
                        value: secondaryColor,
                        onChange: debounce((secondaryColor) => dispatch(setFormSettings({secondaryColor})), 100),
                        label: __('Secondary Color', 'givewp'),
                        disableCustomColors: false,
                    },
                ]}
            />
        </>
    );
};

export default FormDesignSettings;
