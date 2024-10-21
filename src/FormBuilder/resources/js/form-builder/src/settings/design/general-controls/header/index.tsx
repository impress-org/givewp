import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import MediaLibrary from '@givewp/form-builder/components/settings/MediaLibrary';
import {upload} from '@wordpress/icons';
import {ClassicEditor} from '@givewp/form-builder-library';

/**
 * @since 3.16.2 Replace TextareaControl component with ClassicEditor component on the description option
 */
export default function Header({dispatch, publishSettings}) {
    const {
        settings: {
            showHeader,
            showHeading,
            heading,
            showDescription,
            description,
            designSettingsImageUrl,
            designSettingsImageStyle = 'background',
        },
    } = useFormState();

    const resetSettings = () => {
        const reset = {
            designSettingsImageUrl: '',
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
                            })
                        );
                        publishSettings({
                            showHeader: !showHeader,
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
                                    })
                                );
                                publishSettings({
                                    showHeading: !showHeading,
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
                                    })
                                );
                                publishSettings({
                                    showDescription: !showDescription,
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
                                        })
                                    );
                                    publishSettings({
                                        heading,
                                    });
                                }}
                            />
                        </PanelRow>
                    )}
                    {showDescription && (
                        <PanelRow>
                            <ClassicEditor
                                key={'givewp-header-description'}
                                id={'givewp-header-description'}
                                label={__('Description', 'give')}
                                content={description}
                                setContent={(description) => {
                                    dispatch(
                                        setFormSettings({
                                            description,
                                        })
                                    );
                                    publishSettings({
                                        description,
                                    });
                                }}
                                rows={10}
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
                            onChange={(designSettingsImageUrl, designSettingsImageAlt) => {
                                dispatch(
                                    setFormSettings({
                                        designSettingsImageUrl,
                                        designSettingsImageAlt,
                                        designSettingsImageStyle,
                                    })
                                );

                                publishSettings({
                                    designSettingsImageUrl,
                                    designSettingsImageAlt,
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
