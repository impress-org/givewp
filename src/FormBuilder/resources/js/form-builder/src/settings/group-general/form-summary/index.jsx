import { PanelRow, SelectControl, TextControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

import { isFormPageEnabled, PageSlugControl } from "./page-slug";
import { cleanForSlug } from "@wordpress/url";

/**
 * @since 3.1.0 dispatch page slug from form title on initial publish.
 */
const FormSummarySettings = ({settings, setSettings}) => {
    const {formTitle, pageSlug, formStatus, newFormStatus} = settings;
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
        </>
    );
};

export default FormSummarySettings;
