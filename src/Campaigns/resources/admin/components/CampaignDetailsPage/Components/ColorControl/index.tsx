import {ColorIndicator, Popover} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import classnames from 'classnames';

import EditIcon from './Icons/EditIcon';
import CheckIcon from './Icons/CheckIcon';
import './styles.scss';

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
                console.log('field', field);
                return (
                    <div className={classnames('givewp-color-control', className)}>
                        <div className="givewp-color-control__indicator">
                            <ColorIndicator colorValue={field.value} />
                            {field.value && <CheckIcon refColor={field.value} />}
                        </div>

                        {!disabled && (
                            <div className="givewp-color-control__popover">
                                <button className="givewp-color-control__edit-button" onClick={toggleVisible}>
                                    <EditIcon />
                                    {__('Edit', 'give')}
                                </button>
                                {popoverIsVisible && <Popover>This is the content inside the popover</Popover>}
                            </div>
                        )}
                    </div>
                );
            }}
        />
    );
};

export default ColorControl
