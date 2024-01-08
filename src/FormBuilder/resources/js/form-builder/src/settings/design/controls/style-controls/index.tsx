import {__} from '@wordpress/i18n';
import {useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import DesignSettings from '@givewp/form-builder/settings/design/controls/design-settings';
import Color from '@givewp/form-builder/settings/design/controls/style-controls/color';
import CustomStyles from '@givewp/form-builder/settings/design/controls/style-controls/custom-styles';

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
