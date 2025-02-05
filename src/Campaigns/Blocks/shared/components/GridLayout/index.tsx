import {SelectControl} from '@wordpress/components';

import './styles.scss';

/**
 * @unreleased
 */
export default ({label, options, onChange, value, defaultValue}: GridLayoutProps) => {

    const index = options.findIndex((option) => {
        if (defaultValue && !value) {
            return defaultValue === option.value
        }

        return value === option.value;
    });

    return (
        <>
            <div className="give-campaign-components-gridLayout">
                <div className="give-campaign-components-gridLayout__columns">
                    {Array(index + 1).fill(<div className="give-campaign-components-gridLayout__columns-item"></div>)}
                </div>
            </div>

            <SelectControl
                label={label}
                value={value}
                onChange={(selected: string) => onChange(selected)}
                options={options}
                defaultValue={defaultValue}
            />
        </>
    )
}

interface GridLayoutProps {
    label: string;
    value: string;
    options: {
        value: string,
        label: string
    }[],
    onChange: (value: string) => void,
    defaultValue?: string;
}
