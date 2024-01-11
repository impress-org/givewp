import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import ImageUpload from '@givewp/form-builder/components/settings/ImageUpload';
import {upload} from '@wordpress/icons';

export default function Logo({dispatch, publishSettings}) {
    const {
        settings: {designSettingsLogoUrl, designSettingsLogoPosition},
    } = useFormState();

    return (
        <PanelBody title={__('Branding', 'give')} initialOpen={false}>
            <PanelRow>
                <ImageUpload
                    id="givewp-logo-image-control-upload"
                    icon={upload}
                    value={designSettingsLogoUrl}
                    label={__('Logo URL', 'give')}
                    actionLabel={__('Upload Logo', 'give')}
                    onChange={(designSettingsLogoUrl) => {
                        dispatch(setFormSettings({designSettingsLogoUrl}));
                        publishSettings({designSettingsLogoUrl});
                    }}
                />
            </PanelRow>
            <PanelRow>
                <SelectControl
                    label={__('Logo Alignment', 'give')}
                    onChange={(designSettingsLogoPosition) => {
                        dispatch(setFormSettings({designSettingsLogoPosition}));
                        publishSettings({designSettingsLogoPosition});
                    }}
                    value={designSettingsLogoPosition}
                    options={[
                        {label: __('Left', 'give'), value: 'left'},
                        {label: __('Center', 'give'), value: 'center'},
                        {label: __('Right', 'give'), value: 'right'},
                    ]}
                />
            </PanelRow>
        </PanelBody>
    );
}
