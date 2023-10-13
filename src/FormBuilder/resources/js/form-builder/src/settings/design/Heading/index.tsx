import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @since 3.0.0
 */
export default function Heading({heading}: {heading: string}) {
    const {
        inputValue,
        setInputValue,
        updateSetting,
    } = useFormDesignSetting(heading);

    return (
        <TextControl
            label={__('Heading', 'give')}
            value={inputValue}
            onChange={(value) => {
                setInputValue(value);
                updateSetting('heading', value);
            }}
        />
    );
}
