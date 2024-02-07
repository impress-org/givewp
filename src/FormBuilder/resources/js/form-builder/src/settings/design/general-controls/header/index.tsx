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
        },
    } = useFormState();

    const resetSettings = () => {
        const reset = {
            designSettingsImageUrl: '',
            designSettingsImageStyle: '',
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
                            })
                        );
                        publishSettings({
                            showHeader: !showHeader,
                            designSettingsImageUrl,
                            designSettingsImageStyle,
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
                                    })
                                );
                                publishSettings({
                                    showHeading: !showHeading,
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
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
                                    })
                                );
                                publishSettings({
                                    showDescription: !showDescription,
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
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
                                        })
                                    );
                                    publishSettings({
                                        heading,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
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
                                        })
                                    );
                                    publishSettings({
                                        description,
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
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
                                    })
                                );
                                publishSettings({
                                    designSettingsImageUrl,
                                    designSettingsImageStyle,
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
                                        })
                                    );

                                    publishSettings({
                                        designSettingsImageUrl,
                                        designSettingsImageStyle,
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
