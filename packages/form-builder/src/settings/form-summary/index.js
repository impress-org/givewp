import {
    PanelBody,
    PanelRow,
    SelectControl,
    TextControl,
} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {PageSlugControl, isFormPageEnabled} from "./page-slug";

const FormSummarySettings = () => {
    const {
        settings: {formTitle},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody className={'givewp-panel-body--summary'} title={__('Summary', 'give')} initialOpen={true}>
            <PanelRow>
                <TextControl
                    label={__('Form Title')}
                    value={formTitle}
                    onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                />
            </PanelRow>
            { !! isFormPageEnabled && (
                <PanelRow>
                    <PageSlugControl />
                </PanelRow>
            )}
        </PanelBody>
    );
};

export default FormSummarySettings;
