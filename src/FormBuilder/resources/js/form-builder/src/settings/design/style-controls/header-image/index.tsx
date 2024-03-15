import {BaseControl, PanelBody, RangeControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {PanelColorSettings, SETTINGS_DEFAULTS} from '@wordpress/block-editor';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

/**
 * @since 3.6.0
 */
export default function HeaderImage({dispatch}) {
    const {
        settings: {designSettingsImageOpacity, designSettingsImageColor},
    } = useFormState();
    const {publishSettings} = useDonationFormPubSub();

    const handleColorChange = (designSettingsImageColor: string) => {
        if (!designSettingsImageColor) {
            return removeOverlay();
        }

        if (!designSettingsImageOpacity) {
            dispatch(
                setFormSettings({
                    designSettingsImageOpacity: '25',
                })
            );
            publishSettings({designSettingsImageOpacity: '25'});
        }

        dispatch(setFormSettings({designSettingsImageColor}));
        publishSettings({designSettingsImageColor});
    };

    const removeOverlay = () => {
        dispatch(setFormSettings({designSettingsImageColor: ''}));
        publishSettings({
            designSettingsImageColor: '',
        });
    };

    return (
        <PanelBody className={'givewp-header-image-filter'} title={__('Header Image', 'give')}>
            <BaseControl id={'givewp-header-image-filter__control'} label={__('Color', 'give')}>
                <PanelColorSettings
                    colorSettings={[
                        {
                            value: designSettingsImageColor,
                            onChange: handleColorChange,
                            label: __('Overlay', 'give'),
                            disableCustomColors: false,
                            colors: SETTINGS_DEFAULTS.colors,
                        },
                    ]}
                />
            </BaseControl>

            {designSettingsImageColor && (
                <BaseControl id={'givewp-header-image-filter__range-control'} label={__('Overlay Opacity', 'give')}>
                    <RangeControl
                        value={designSettingsImageOpacity ? Number(designSettingsImageOpacity) : 25}
                        onChange={(designSettingsImageOpacity: number) => {
                            dispatch(setFormSettings({designSettingsImageOpacity: String(designSettingsImageOpacity)}));
                            publishSettings({
                                designSettingsImageOpacity: String(designSettingsImageOpacity),
                            });
                        }}
                        min={0}
                        max={100}
                    />
                </BaseControl>
            )}
        </PanelBody>
    );
}
