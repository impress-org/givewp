import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function Header({dispatch, publishSettings}) {
    const {
        settings: {
            showHeader,
            showHeading,
            heading,
            showDescription,
            description,
        },
    } = useFormState();

    return (
        <PanelBody title={__('Header', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Show Header', 'give')}
                    checked={showHeader}
                    onChange={() => {
                        dispatch(
                            setFormSettings({
                                showHeader: !showHeader
                            })
                        );
                        publishSettings({
                            showHeader: !showHeader
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
                                        showHeading: !showHeading
                                    })
                                );
                                publishSettings({
                                    showHeading: !showHeading
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
                                        showDescription: !showDescription
                                    })
                                );
                                publishSettings({
                                    showDescription: !showDescription
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
                                            heading
                                        })
                                    );
                                    publishSettings({
                                        heading
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
                                            description
                                        })
                                    );
                                    publishSettings({
                                        description
                                    });
                                }}
                            />
                        </PanelRow>
                    )}
                </>
            )}
        </PanelBody>
    );
}
