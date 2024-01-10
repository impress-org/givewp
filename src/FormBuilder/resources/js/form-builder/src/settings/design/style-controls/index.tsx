import {__} from '@wordpress/i18n';
import {useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import Color from '@givewp/form-builder/settings/design/style-controls/color';
import CustomStyles from '@givewp/form-builder/settings/design/style-controls/custom-styles';
import DesignSettings from '@givewp/form-builder/components/settings/DesignSettings';

/**
 * @unreleased abstract design controls.
 */
export default function StyleControls() {
    const dispatch = useFormStateDispatch();

    return (
        <DesignSettings
            title={__('Advanced', 'give')}
            description={__(
                'These settings gives you the ability to change the overall appearance of your form to suit your brand',
                'give'
            )}
        >
            <Color dispatch={dispatch} />
            <CustomStyles />
        </DesignSettings>
    );
}
