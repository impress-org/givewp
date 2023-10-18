import {PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {isFormPageEnabled, PageSlugControl} from './page-slug';

const FormSummarySettings = () => {
    const {
        settings: {formTitle, formStatus},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody className={'givewp-panel-body--summary'} title={__('Summary', 'give')} initialOpen={true}>
            <PanelRow>
                <TextControl
                    label={__('Title')}
                    value={formTitle}
                    onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                />
            </PanelRow>
            {!!isFormPageEnabled && (
                <PanelRow>
                    <PageSlugControl />
                </PanelRow>
            )}
            <PanelRow>
                <SelectControl
                    label={__('Visibility', 'give')}
                    value={formStatus}
                    options={[
                        {label: __('Public', 'give'), value: 'publish'},
                        {label: __('Private', 'give'), value: 'private'},
                    ]}
                    onChange={(formStatus) => dispatch(setFormSettings({formStatus}))}
                />
            </PanelRow>
            <PanelRow className="givewp-next-gen-prepublish-panel_visibility">
                {['publish', 'draft'].includes(formStatus) ? __('Visible to everyone', 'give') : __('Only visible to site admins and editors', 'give')}
            </PanelRow>
        </PanelBody>
    );
};

export default FormSummarySettings;
