import {PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {isFormPageEnabled, PageSlugControl} from './page-slug';
import {cleanForSlug} from '@wordpress/url';

/**
 * @since 3.1.0 dispatch page slug from form title on initial publish.
 */
const FormSummarySettings = () => {
    const {
        settings: {formTitle, pageSlug, formStatus},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const isPublished = 'publish' === formStatus;
    const isTitleSlug = !isPublished && cleanForSlug(formTitle) === pageSlug;

    return (
        <div className={'givewp-form-settings__section__body__extra-gap'}>
            <PanelRow>
                <TextControl
                    label={__('Form name', 'give')}
                    value={formTitle}
                    onChange={(formTitle) => {
                        !isPublished && dispatch(setFormSettings({pageSlug: cleanForSlug(formTitle)}));
                        dispatch(setFormSettings({formTitle}));
                    }}
                />
            </PanelRow>

            {!!isFormPageEnabled && (
                <PageSlugControl pageSlug={isTitleSlug ? cleanForSlug(formTitle) : pageSlug} />
            )}
        </div>
    );
};

export default FormSummarySettings;
