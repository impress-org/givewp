import {__} from '@wordpress/i18n';
import {useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import DonationGoal from './donation-goal';
import Header from '@givewp/form-builder/settings/design/general-controls/header';
import Layout from '@givewp/form-builder/settings/design/general-controls/layout';
import {getWindowData} from '@givewp/form-builder/common';
import DonateButton from '@givewp/form-builder/settings/design/general-controls/donate-button';
import MultiStep from '@givewp/form-builder/settings/design/general-controls/multi-step';
import DesignSettings from '@givewp/form-builder/components/settings/DesignSettings';

const {formDesigns} = getWindowData();
const getDesign = (designId: string) => formDesigns[designId];

/**
 * @since 3.4.0 abstract design controls.
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
            <Layout
                dispatch={dispatch}
                formDesigns={formDesigns}
                designId={designId}
            />
            <Header dispatch={dispatch} publishSettings={publishSettings} />
            {design?.isMultiStep && <MultiStep dispatch={dispatch} publishSettings={publishSettings} />}
            <DonationGoal dispatch={dispatch} />
            {!design?.isMultiStep && <DonateButton dispatch={dispatch} publishSettings={publishSettings} />}
        </DesignSettings>
    );
}
