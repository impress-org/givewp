import {__} from '@wordpress/i18n';
import {ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {getWindowData} from '@givewp/form-builder/common';

/**
 * @since 4.1.0
 */
export default function ColorInheritanceToggle({dispatch, children}) {
    const {
        settings: {primaryColor, secondaryColor, inheritCampaignColors = false},
    } = useFormState();

    const {campaignColors} = getWindowData();
    const {publishColors} = useDonationFormPubSub();
    const hasCampaignColors = !!(campaignColors.primaryColor && campaignColors.secondaryColor);

    const handleToggleChange = (inheritCampaignColors) => {
        dispatch(setFormSettings({inheritCampaignColors: inheritCampaignColors}));

        if (inheritCampaignColors) {
            if (campaignColors.primaryColor) {
                publishColors({primaryColor: campaignColors.primaryColor});
            }

            if (campaignColors.secondaryColor) {
                publishColors({secondaryColor: campaignColors.secondaryColor});
            }
        } else {
            publishColors({primaryColor, secondaryColor});
        }
    };

    return (
        <>
            {hasCampaignColors && (
                <ToggleControl
                    label={__('Inherit Campaign Colors', 'give')}
                    checked={inheritCampaignColors}
                    onChange={handleToggleChange}
                    help={__('Inherit the colors from the campaign settings.', 'give')}
                />
            )}

            {(!inheritCampaignColors || !hasCampaignColors) && children}
        </>
    );
}
