import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {isFormPageEnabled, PageSlugControl} from './page-slug';
import removeHtmlTags from '@givewp/form-builder/common/removeHtmlTags';

const FormSummarySettings = () => {
    const {
        settings: {formTitle},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody className={'givewp-panel-body--summary'} title={__('Summary', 'give')} initialOpen={true}>
            <PanelRow>
                <TextControl
                    label={__('Title')}
                    value={formTitle}
                    onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                    onBlur={(event) => {
                        const cleanedFormTitle = removeHtmlTags(event.target.value);
                        dispatch(setFormSettings({formTitle: cleanedFormTitle}));
                    }}
                />
            </PanelRow>
            {!!isFormPageEnabled && (
                <PanelRow>
                    <PageSlugControl />
                </PanelRow>
            )}
        </PanelBody>
    );
};

export default FormSummarySettings;
