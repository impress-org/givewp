import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {isFormPageEnabled, PageSlugControl} from './page-slug';
import {cleanForSlug} from '@wordpress/url';

/**
 * @unreleased dispatch page slug from form title on initial publish.
 */
const FormSummarySettings = () => {
    const {
        settings: {formTitle, pageSlug, formStatus},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const isPublished = 'publish' === formStatus;
    const isTitleSlug = isPublished && cleanForSlug(formTitle) === pageSlug;

    return (
        <PanelBody className={'givewp-panel-body--summary'} title={__('Summary', 'give')} initialOpen={true}>
            <PanelRow>
                <TextControl
                    label={__('Title')}
                    value={formTitle}
                    onChange={(formTitle) => {
                        !isPublished && dispatch(setFormSettings({pageSlug: cleanForSlug(formTitle)}));
                        dispatch(setFormSettings({formTitle}));
                    }}
                />
            </PanelRow>
            {!!isFormPageEnabled && (
                <PanelRow>
                    <PageSlugControl pageSlug={isTitleSlug ? cleanForSlug(formTitle) : pageSlug} />
                </PanelRow>
            )}
        </PanelBody>
    );
};

export default FormSummarySettings;
