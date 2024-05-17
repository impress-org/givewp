import {PanelRow, SelectControl, TextareaControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';

import {isFormPageEnabled, PageSlugControl} from './page-slug';
import {cleanForSlug} from '@wordpress/url';
import {getWindowData} from '@givewp/form-builder/common';

const {isExcerptEnabled} = getWindowData();

/**
 * @since 3.7.0 Added formExcerpt text area
 * @since 3.1.0 dispatch page slug from form title on initial publish.
 */
const FormSummarySettings = ({settings, setSettings}) => {
    const {formTitle, pageSlug, formStatus, newFormStatus, formExcerpt} = settings;
    const isPublished = ['publish', 'private'].includes(formStatus);
    const isTitleSlug = !isPublished && cleanForSlug(formTitle) === pageSlug;

    const isPrivate = () => {
        if (newFormStatus) {
            return 'private' === newFormStatus;
        }

        return 'private' === formStatus;
    };

    return (
        <>
            <PanelRow>
                <TextControl
                    label={__('Title')}
                    value={formTitle}
                    onChange={(formTitle) => {
                        !isPublished && setSettings({pageSlug: cleanForSlug(formTitle)});
                        setSettings({formTitle});
                    }}
                />
            </PanelRow>

            {!!isFormPageEnabled && <PageSlugControl pageSlug={isTitleSlug ? cleanForSlug(formTitle) : pageSlug} />}

            <PanelRow>
                <SelectControl
                    label={__('Visibility', 'give')}
                    help={
                        isPrivate()
                            ? __('Only visible to site admins and editors', 'give')
                            : __('Visible to everyone', 'give')
                    }
                    value={newFormStatus ?? ('draft' === formStatus ? 'publish' : formStatus)}
                    options={[
                        {label: __('Public', 'give'), value: 'publish'},
                        {label: __('Private', 'give'), value: 'private'},
                    ]}
                    onChange={(newFormStatus) => setSettings({newFormStatus})}
                />
            </PanelRow>

            {isExcerptEnabled && (
                <PanelRow>
                    <TextareaControl
                        label={'Excerpt'}
                        help={__(
                            'The excerpt is an optional summary or description of a donation form; in short, a summary as to why the user should give.',
                            'give'
                        )}
                        value={formExcerpt}
                        onChange={(formExcerpt) => setSettings({formExcerpt})}
                    />
                </PanelRow>
            )}
        </>
    );
};

export default FormSummarySettings;
