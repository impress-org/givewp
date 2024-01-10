import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelBody, SelectControl} from '@wordpress/components';

export default function Image() {
    const {
        settings: {
            designSettingsImageUrl,
            designSettingsImageStyle,
        },
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {publishSettings} = useDonationFormPubSub();

    return (
        <PanelBody title={__('Image', 'give')}>
            <SelectControl
                label={__('Image Style', 'give')}
                onChange={(designSettingsImageStyle) => {
                    dispatch(setFormSettings({designSettingsImageStyle}));
                    publishSettings({designSettingsImageStyle});
                }}
                value={designSettingsImageStyle}
                options={[
                    {label: __('Background', 'give'), value: 'background'},
                    {label: __('Cover', 'give'), value: 'cover'},
                    {label: __('Above', 'give'), value: 'above'},
                    {label: __('Center', 'give'), value: 'center'},

                ]}
            />
        </PanelBody>
    );
}
