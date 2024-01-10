import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelBody, SelectControl} from '@wordpress/components';
import MediaLibrary from "@givewp/form-builder/components/media-library";

export default function Logo() {
    const {
        settings: {
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {publishSettings} = useDonationFormPubSub();

    return (
        <PanelBody title={__('Logo', 'give')}>
            <MediaLibrary
                label={__('Logo URL', 'give')}
                value={designSettingsLogoUrl}
                onChange={(designSettingsLogoUrl) => {
                    dispatch(setFormSettings({designSettingsLogoUrl}));
                    publishSettings({designSettingsLogoUrl});
                }}
                help={__(
                    'Upload or choose an logo to be displayed in the form.',
                    'give'
                )}
            />
            <SelectControl
                label={__('Logo Alignment', 'give')}
                onChange={(designSettingsLogoPosition) => {
                    dispatch(setFormSettings({designSettingsLogoPosition}))
                    publishSettings({designSettingsLogoPosition})
                }}
                value={designSettingsLogoPosition}
                options={[
                    {label: __('Left', 'give'), value: 'left'},
                    {label: __('Center', 'give'), value: 'center'},
                    {label: __('Right', 'give'), value: 'right'},
                ]}
            />
        </PanelBody>
    );
}
