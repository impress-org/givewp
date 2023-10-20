import {useCallback, useState} from 'react';
import {useDebounce} from '@wordpress/compose';
import {setFormSettings, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {FormSettings} from '@givewp/form-builder/types';
import useIframeMessages from '@givewp/forms/app/utilities/IframeMessages';

/**
 * This hook is used to manage the state of a form design setting.
 * It is intended to be used with controlled inputs, to track the internal state of the input,
 * while debouncing the form design setting value.
 *
 * @since 3.0.0
 */
const useFormDesignSetting = (initialValue: any, wait = 500) => {
    const [inputValue, setInputValue] = useState(initialValue);
    const dispatch = useFormStateDispatch();
    const {sendToIframe} = useIframeMessages();

    const updateSetting = useCallback((key: keyof FormSettings, value: any) => {
        dispatch(setFormSettings({[key]: value}));
        sendToIframe('iFrameResizer0', 'designPreview', {[key]: value});
    }, []);

    return {
        inputValue,
        setInputValue,
        updateSetting: useDebounce(updateSetting, wait),
    };
};

export default useFormDesignSetting;
