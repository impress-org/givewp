import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {upload} from '@wordpress/icons';
import MediaLibrary from '@givewp/form-builder/components/settings/MediaLibrary';

export default function Logo({dispatch, publishSettings}) {
    const {
        settings: {designSettingsLogoUrl, designSettingsLogoPosition, designSettingsImageUrl, designSettingsImageStyle},
    } = useFormState();

    const resetSettings = () => {
        const reset = {
            designSettingsLogoUrl: '',
            designSettingsLogoPosition: '',
            designSettingsImageUrl,
            designSettingsImageStyle,
        }

        dispatch(setFormSettings(reset));
        publishSettings(reset);
    }

    return (
        <PanelBody title={__('Branding', 'give')} initialOpen={false}>
            <PanelRow>
                <MediaLibrary
                    id="givewp-logo-media-library-control"
                    label={__('Logo URL', 'give')}
                    icon={upload}
                    value={designSettingsLogoUrl}
                    actionLabel={__('Upload Logo', 'give')}
                    onChange={(designSettingsLogoUrl) => {
                        dispatch(setFormSettings({
                            designSettingsImageUrl,
                            designSettingsImageStyle,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition
                        }));

                        publishSettings({
                            designSettingsImageUrl,
                            designSettingsImageStyle,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition
                        });
                    }}
                    reset={resetSettings}
                />
            </PanelRow>
            {designSettingsLogoUrl && (
                <PanelRow>
                    <SelectControl
                        label={__('Logo Alignment', 'give')}
                        onChange={(designSettingsLogoPosition) => {
                            dispatch(setFormSettings({
                                designSettingsImageUrl,
                                designSettingsImageStyle,
                                designSettingsLogoUrl,
                                designSettingsLogoPosition
                            }));

                            publishSettings({
                                designSettingsImageUrl,
                                designSettingsImageStyle,
                                designSettingsLogoUrl,
                                designSettingsLogoPosition
                            });
                        }}
                        value={designSettingsLogoPosition}
                        options={[
                            {label: __('Left', 'give'), value: 'left'},
                            {label: __('Center', 'give'), value: 'center'},
                            {label: __('Right', 'give'), value: 'right'},
                        ]}
                    />
                </PanelRow>
            )}
        </PanelBody>
    );
}
