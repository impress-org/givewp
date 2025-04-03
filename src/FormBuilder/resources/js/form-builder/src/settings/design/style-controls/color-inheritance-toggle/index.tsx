import {__} from '@wordpress/i18n';
import {ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

export default function ColorInheritanceToggle({dispatch, children}) {
    const {
        settings: {inheritCampaignColors},
    } = useFormState();

    const {publishColors} = useDonationFormPubSub();

    // TODO: Retrieve campaign colors from settings then pass them to the publishColors function

    return (
        <>
            <ToggleControl
                label={__('Inherit Campaign Colors', 'give')}
                checked={inheritCampaignColors ?? false}
                onChange={(inheritCampaignColors: boolean) => {
                    console.log('inheritCampaignColors', inheritCampaignColors);
                    dispatch(setFormSettings({inheritCampaignColors}));
                    //publishColors(value);
                }}
                help={__('Inherit the colors from the campaign settings.', 'give')}
            />

            {!inheritCampaignColors && children}
        </>
    );
}
