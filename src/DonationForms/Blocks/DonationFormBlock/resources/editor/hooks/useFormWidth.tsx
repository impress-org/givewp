import {useState} from 'react';

/**
 * @unreleased
 */
export default function useFormWidth(): {formWidth: string; getFormWidth: () => void} {
    const [formWidth, setFormWidth] = useState('');

    const getFormWidth = () => {
        const iframe = document.querySelector('iframe');
        const width = iframe.contentWindow.document.getElementById('root-givewp-donation-form').scrollWidth;
        setFormWidth(String(width));
    };

    return {
        getFormWidth,
        formWidth,
    };
}
