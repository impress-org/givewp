import {PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {isFormPageEnabled, PageSlugControl} from './page-slug';
import {cleanForSlug} from '@wordpress/url';

/**
 * @unreleased dispatch page slug from form title on initial publish.
 */
const FormSummarySettings = () => {
    const {
        settings: {formTitle, pageSlug, formStatus, newFormStatus},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const isPublished = 'publish' === formStatus;
    const isTitleSlug = !isPublished && cleanForSlug(formTitle) === pageSlug;

    const isPrivate = () => {
        if (newFormStatus) {
            return 'private' === newFormStatus;
        }

        return 'private' === formStatus;
    }

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
            <PanelRow>
                <SelectControl
                    label={__('Visibility', 'give')}
                    value={newFormStatus ?? ('draft' === formStatus ? 'publish' : formStatus)}
                    options={[
                        {label: __('Public', 'give'), value: 'publish'},
                        {label: __('Private', 'give'), value: 'private'},
                    ]}
                    onChange={(newFormStatus) => dispatch(setFormSettings({newFormStatus}))}
                />
            </PanelRow>
            <PanelRow className="givewp-next-gen-prepublish-panel_visibility">
                {isPrivate() ? __('Only visible to site admins and editors', 'give') : __('Visible to everyone', 'give')}
            </PanelRow>
        </PanelBody>
    );
};

export default FormSummarySettings;
