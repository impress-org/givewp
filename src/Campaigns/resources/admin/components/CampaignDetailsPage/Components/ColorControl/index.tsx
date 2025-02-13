import {ColorIndicator, ColorPalette, Popover} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import classnames from 'classnames';

import EditIcon from './Icons/EditIcon';
import CheckIcon from './Icons/CheckIcon';
import './styles.scss';

const defaultColors = [
    {name: 'Blue', slug: 'blue', color: '#0b72d9'},
    {name: 'Green', slug: 'green', color: '#27ae60'},
    {name: 'Purple', slug: 'purple', color: '#19078c'},
    {name: 'Orange', slug: 'orange', color: '#f29718'},
    {name: 'Lavender', slug: 'lavender', color: '#9b51e0'},
    {name: 'Terracotta', slug: 'terracotta', color: '#e26f56'},
    {name: 'Red', slug: 'red', color: '#cc1818'},
];

const ColorControl = ({name, disabled, className}) => {
    const [popoverIsVisible, setPopoverIsVisible] = useState<boolean>(false);
    const {control} = useFormContext();

    const toggleVisible = () => {
        setPopoverIsVisible((state) => !state);
    };

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => {
                return (
                    <div className={classnames('givewp-color-control', className)}>
                        <div className="givewp-color-control__indicator">
                            <ColorIndicator colorValue={field.value} />
                            {field.value && <CheckIcon refColor={field.value} />}
                        </div>

                        {!disabled && (
                            <div className="givewp-color-control__popover">
                                <button
                                    className={classnames('givewp-color-control__edit-button', {
                                        'givewp-color-control__edit-button--active': popoverIsVisible,
                                    })}
                                    onClick={toggleVisible}
                                >
                                    <EditIcon />
                                    {__('Edit', 'give')}
                                </button>
                                {popoverIsVisible && (
                                    <Popover
                                        className="givewp-color-control__popover-content"
                                        offset={8}
                                        onClose={toggleVisible}
                                        placement="right"
                                    >
                                        <ColorPalette
                                            clearable={false}
                                            colors={[
                                                {
                                                    colors: defaultColors,
                                                    name: __('Theme', 'give'),
                                                },
                                            ]}
                                            value={field.value}
                                            onChange={(color) => field.onChange(color)}
                                        />
                                    </Popover>
                                )}
                            </div>
                        )}
                    </div>
                );
            }}
        />
    );
};

export default ColorControl
