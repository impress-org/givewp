import {__} from '@wordpress/i18n';
import {useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import Color from './color';
import CustomStyles from './custom-styles';
import SectionSettings from './section';
import DesignSettings from '@givewp/form-builder/components/settings/DesignSettings';
import HeaderSettings from '@givewp/form-builder/settings/design/style-controls/header';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

/**
 * @unreleased abstract design controls.
 */
export default function StyleControls() {
    const dispatch = useFormStateDispatch();
    const {publishSettings} = useDonationFormPubSub();

    return (
        <DesignSettings
            title={__('Advanced', 'give')}
            description={__(
                'These settings gives you the ability to change the overall appearance of your form to suit your brand',
                'give'
            )}
        >
            <Color dispatch={dispatch} />
            <HeaderSettings dispatch={dispatch} publishSettings={publishSettings} />
            <SectionSettings />
            <CustomStyles />
        </DesignSettings>
    );
}
