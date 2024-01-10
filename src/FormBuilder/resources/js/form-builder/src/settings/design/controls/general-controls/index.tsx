import {__} from '@wordpress/i18n';
import {useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import DonationGoal from './donation-goal';
import DesignSettings from '@givewp/form-builder/settings/design/controls/design-settings';
import Header from '@givewp/form-builder/settings/design/controls/general-controls/header';
import Layout from '@givewp/form-builder/settings/design/controls/general-controls/layout';
import {getWindowData} from '@givewp/form-builder/common';
import DonateButton from '@givewp/form-builder/settings/design/controls/general-controls/donate-button';
import MultiStep from '@givewp/form-builder/settings/design/controls/general-controls/multi-step';

const {formDesigns} = getWindowData();
const getDesign = (designId: string) => formDesigns[designId];

/**
 * @unreleased abstract design controls.
 */
export default function GeneralControls() {
    const {
        settings: {designId},
    } = useFormState();

    const dispatch = useFormStateDispatch();
    const {publishSettings} = useDonationFormPubSub();
    const design = getDesign(designId);

    return (
        <DesignSettings
            title={__('General', 'give')}
            description={__('These settings affect the basic appearance of the form', 'give')}
        >
            <Layout dispatch={dispatch} formDesigns={formDesigns} designId={designId} />
            {design?.isMultiStep && <MultiStep dispatch={dispatch} publishSettings={publishSettings} />}
            <Header dispatch={dispatch} publishSettings={publishSettings} />
            <DonationGoal dispatch={dispatch} />
            <DonateButton dispatch={dispatch} publishSettings={publishSettings} />
        </DesignSettings>
    );
}
