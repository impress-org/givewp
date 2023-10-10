import {useCallback, useState} from 'react';
import {useDebounce} from '@wordpress/compose';
import {setFormSettings, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {FormSettings} from '@givewp/form-builder/types';

/**
 * @unreleased
 */
const useFormDesignSetting = (initialValue: any, wait = 500) => {
    const [inputValue, setInputValue] = useState(initialValue);
    const dispatch = useFormStateDispatch();

    const updateSetting = useCallback((key: keyof FormSettings, value: any) => {
        dispatch(setFormSettings({[key]: value}));
    }, []);

    return {
        inputValue,
        setInputValue,
        updateSetting: useDebounce(updateSetting, wait),
    };
};

export default useFormDesignSetting;
