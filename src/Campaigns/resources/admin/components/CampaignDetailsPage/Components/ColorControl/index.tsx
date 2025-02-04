import {Popover} from '@wordpress/components';
import {useState} from 'react';
import {Controller, useFormContext} from 'react-hook-form';

const ColorControl = ({name, isDisabled}) => {
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
                    <div className="givewp-color-control">
                        <div className="givewp-color-control__popover">
                            <button onClick={toggleVisible}>Toggle</button>
                            {popoverIsVisible && <Popover>This is the content inside the popover</Popover>}
                        </div>
                    </div>
                );
            }}
        />
    );
};

export default ColorControl
