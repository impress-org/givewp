import {useEffect} from '@wordpress/element';

type AttributesDefinitions = {
    [key: string]: {
        type?: string;
        enum?: string[];
        default?: any;
    };
};

export default function useSetDefaultAttributes(
    attributes: {[key: string]: any},
    setAttributes: (attributes: {[key: string]: any}) => void,
    attributesDefinitions: AttributesDefinitions
) {
    useEffect(() => {
        const attributesWithDefaults = Object.keys(attributesDefinitions).reduce((acc, key) => {
            if (!attributes.hasOwnProperty(key) && attributesDefinitions[key].hasOwnProperty('default')) {
                acc[key] = attributesDefinitions[key]['default'];
            }
            return acc;
        }, {});

        setAttributes(attributesWithDefaults);
    }, []);
}
