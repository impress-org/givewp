import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, SelectControl, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {upload} from '@wordpress/icons';
import MediaLibrary from '@givewp/form-builder/components/settings/MediaLibrary';

export default function Header({dispatch, publishSettings}) {
    const {
        settings: {
            showHeader,
            showHeading,
            heading,
            showDescription,
            description,
            designSettingsImageUrl,
            designSettingsImageStyle,
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();

    const resetSettings = () => {
        const reset = {
            designSettingsImageUrl: '',
            designSettingsImageStyle: '',
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        };

        dispatch(setFormSettings(reset));
        publishSettings(reset);
    };

    return (
        <PanelBody title={__('Header', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Show Header', 'give')}
                    checked={showHeader}
                    onChange={() => {
                        dispatch(
                            setFormSettings({
                                showHeader: !showHeader,
                                designSettingsImageUrl,
                                designSettingsImageStyle,
                                designSettingsLogoUrl,
                                designSettingsLogoPosition,
                            })
                        );
                        publishSettings({
                            showHeader: !showHeader,
                            designSettingsImageUrl,
                            designSettingsImageStyle,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition,
                        });
                    }}
                />
            </PanelRow>

            {showHeader && (
                <>
                    <PanelRow>
                        <ToggleControl
                            label={__('Show Heading', 'give')}
                            checked={showHeading}
                            onChange={() => {
                                dispatch(
                                    setFormSettings({
                                        showHeading: !showHeading,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    })
                                );
                                publishSettings({
                                    showHeading: !showHeading,
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
                                    designSettingsLogoUrl,
                                    designSettingsLogoPosition,
                                });
                            }}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Show Description', 'give')}
                            checked={showDescription}
                            onChange={() => {
                                dispatch(
                                    setFormSettings({
                                        showDescription: !showDescription,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    })
                                );
                                publishSettings({
                                    showDescription: !showDescription,
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
                                    designSettingsLogoUrl,
                                    designSettingsLogoPosition,
                                });
                            }}
                        />
                    </PanelRow>
                    {showHeading && (
                        <PanelRow>
                            <TextControl
                                label={__('Heading', 'give')}
                                value={heading}
                                onChange={(heading) => {
                                    dispatch(
                                        setFormSettings({
                                            heading,
                                            designSettingsImageUrl,
                                            designSettingsImageStyle,
                                            designSettingsLogoUrl,
                                            designSettingsLogoPosition,
                                        })
                                    );
                                    publishSettings({
                                        heading,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    });
                                }}
                            />
                        </PanelRow>
                    )}
                    {showDescription && (
                        <PanelRow>
                            <TextareaControl
                                label={__('Description', 'give')}
                                value={description}
                                onChange={(description) => {
                                    dispatch(
                                        setFormSettings({
                                            description,
                                            designSettingsImageUrl,
                                            designSettingsImageStyle,
                                            designSettingsLogoUrl,
                                            designSettingsLogoPosition,
                                        })
                                    );
                                    publishSettings({
                                        description,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    });
                                }}
                            />
                        </PanelRow>
                    )}
                    <PanelRow>
                        <MediaLibrary
                            id="givewp-header-media-library-control"
                            icon={upload}
                            label={__('Image', 'give')}
                            actionLabel={__('Upload Image', 'give')}
                            value={designSettingsImageUrl}
                            onChange={(designSettingsImageUrl) => {
                                dispatch(
                                    setFormSettings({
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    })
                                );

                                publishSettings({
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
                                    designSettingsLogoUrl,
                                    designSettingsLogoPosition,
                                });
                            }}
                            reset={resetSettings}
                        />
                    </PanelRow>
                    {designSettingsImageUrl && (
                        <PanelRow>
                            <SelectControl
                                label={__('Image Style', 'give')}
                                help={__(
                                    'Determines how the image will be applied in the header for this form.',
                                    'give'
                                )}
                                onChange={(designSettingsImageStyle) => {
                                    dispatch(
                                        setFormSettings({
                                            designSettingsImageUrl,
                                            designSettingsImageStyle,
                                            designSettingsLogoUrl,
                                            designSettingsLogoPosition,
                                        })
                                    );

                                    publishSettings({
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
                                        designSettingsLogoUrl,
                                        designSettingsLogoPosition,
                                    });
                                }}
                                value={designSettingsImageStyle}
                                options={[
                                    {label: __('Background', 'give'), value: 'background'},
                                    {label: __('Above', 'give'), value: 'above'},
                                    {label: __('Center', 'give'), value: 'center'},
                                ]}
                            />
                        </PanelRow>
                    )}
                </>
            )}
        </PanelBody>
    );
}
