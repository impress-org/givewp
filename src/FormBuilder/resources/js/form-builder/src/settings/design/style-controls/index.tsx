import {__} from '@wordpress/i18n';
import {useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import Color from './color';
import CustomStyles from './custom-styles';
import HeaderImage from './header-image';
import DesignSettings from '@givewp/form-builder/components/settings/DesignSettings';
import {getWindowData} from '@givewp/form-builder/common';

const {formDesigns} = getWindowData();
const getDesign = (designId: string) => formDesigns[designId];

/**
 * @since 3.4.0 abstract design controls.
 */
export default function StyleControls() {
    const dispatch = useFormStateDispatch();

    const {
        settings: {},
    } = useFormState();

    return (
        <DesignSettings
            title={__('Advanced', 'give')}
            description={__(
                'These settings gives you the ability to change the overall appearance of your form to suit your brand',
                'give'
            )}
        >
            <Color dispatch={dispatch} />
            <HeaderImage dispatch={dispatch} />
            <CustomStyles />
        </DesignSettings>
    );
}
