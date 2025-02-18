/**
 * External dependencies
 */
import classnames from 'classnames';
import {useCallback, useState} from 'react';
import {Controller, useFormContext} from 'react-hook-form';

/**
 * WordPress dependencies
 */
import {ColorIndicator, ColorPalette, Popover} from '@wordpress/components';
import {__} from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EditIcon from './Icons/EditIcon';
import CheckIcon from './Icons/CheckIcon';
import './styles.scss';

interface ColorOption {
    name: string;
    slug: string;
    color: string;
}

const defaultColors: ColorOption[] = [
    {name: 'Blue', slug: 'blue', color: '#0b72d9'},
    {name: 'Green', slug: 'green', color: '#27ae60'},
    {name: 'Purple', slug: 'purple', color: '#19078c'},
    {name: 'Orange', slug: 'orange', color: '#f29718'},
    {name: 'Lavender', slug: 'lavender', color: '#9b51e0'},
    {name: 'Terracotta', slug: 'terracotta', color: '#e26f56'},
    {name: 'Red', slug: 'red', color: '#cc1818'},
];

/**
 * @unreleased
 */
function ColorControl({name, disabled = false, className}: { name: string; disabled?: boolean; className?: string }) {
    const [popoverIsVisible, setPopoverIsVisible] = useState<boolean>(false);
    const {control} = useFormContext();

    const toggleVisible = useCallback(() => {
        setPopoverIsVisible((prev) => !prev);
    }, []);

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <div className={classnames('givewp-color-control', className)}>
                    <div className="givewp-color-control__indicator">
                        <ColorIndicator colorValue={field.value} />
                        {field.value && <CheckIcon refColor={field.value} />}
                    </div>

                    {!disabled && (
                        <div className="givewp-color-control__popover">
                            <button
                                type="button"
                                className={classnames('givewp-color-control__edit-button', {
                                    'givewp-color-control__edit-button--active': popoverIsVisible,
                                })}
                                onClick={toggleVisible}
                                aria-label={__('Edit color', 'give')}
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
                                        onChange={field.onChange}
                                    />
                                </Popover>
                            )}
                        </div>
                    )}
                </div>
            )}
        />
    );
};

export default ColorControl;
