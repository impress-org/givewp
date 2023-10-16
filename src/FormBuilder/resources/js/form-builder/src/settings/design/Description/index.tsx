import {TextareaControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @since 3.0.0
 */
export default function Description({description}: {description: string}) {
    const {
        inputValue,
        setInputValue,
        updateSetting,
    } = useFormDesignSetting(description);

    return (
        <TextareaControl
            label={__('Description', 'give')}
            value={inputValue}
            onChange={(value) => {
                setInputValue(value);
                updateSetting('description', value);
            }}
        />
    );
}
