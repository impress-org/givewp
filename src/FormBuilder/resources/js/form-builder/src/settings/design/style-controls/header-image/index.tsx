import {BaseControl, Button, PanelBody, RangeControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import EmptyIcon from '@givewp/form-builder/components/icons/empty';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {PanelColorSettings, SETTINGS_DEFAULTS} from '@wordpress/block-editor';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

export default function HeaderImage({dispatch}) {
    const {
        settings: {designSettingsImageOpacity, designSettingsImageColor},
    } = useFormState();
    const {publishSettings} = useDonationFormPubSub();

    const removeBlankSlate = () => {
        dispatch(setFormSettings({designSettingsImageColor: '#000'}));
        publishSettings({
            designSettingsImageColor: '#000',
            designSettingsImageOpacity: 25,
        });
    };

    return (
        <PanelBody className={'givewp-header-styles'} title={__('Header Image', 'give')}>
            <BaseControl id={'givewp-header-styles-duotone-control'} label={__('Filter', 'give')}>
                {designSettingsImageColor ? (
                    <PanelColorSettings
                        colorSettings={[
                            {
                                value: designSettingsImageColor,
                                onChange: (designSettingsImageColor: string) => {
                                    dispatch(setFormSettings({designSettingsImageColor}));
                                    publishSettings({
                                        designSettingsImageColor,
                                    });
                                },
                                label: __('Image Overlay Color', 'give'),
                                disableCustomColors: false,
                                colors: SETTINGS_DEFAULTS.colors,
                            },
                        ]}
                    />
                ) : (
                    <Button className={'givewp-header-styles__button'} onClick={removeBlankSlate} icon={<EmptyIcon />}>
                        {__('Duotone', 'give')}
                    </Button>
                )}
            </BaseControl>

            {designSettingsImageColor && (
                <BaseControl id={'givewp-header-styles-range-control'} label={__('Opacity', 'give')}>
                    <RangeControl
                        currentInput={25}
                        initialPosition={25}
                        value={designSettingsImageOpacity}
                        onChange={(designSettingsImageOpacity: number) => {
                            dispatch(setFormSettings({designSettingsImageOpacity}));
                            publishSettings({
                                designSettingsImageOpacity,
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
