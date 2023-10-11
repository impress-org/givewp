import {TextareaControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @unreleased
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
