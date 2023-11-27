import cn from 'classnames';
import {__} from '@wordpress/i18n';
import {Button, Icon, Tooltip} from '@wordpress/components';

import {draggable, minusCircle} from './icons';
import {OptionsItemProps} from './types';
import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';

export default function OptionsItem({
    currency,
    provided,
    option,
    showValues,
    multiple,
    selectable,
    defaultTooltip,
    handleUpdateOptionLabel,
    handleUpdateOptionValue,
    handleUpdateOptionChecked,
    handleRemoveOption,
    readOnly,
}: OptionsItemProps) {
    return (
        <div className={'givewp-options-list--item'} ref={provided.innerRef} {...provided.draggableProps}>
            <span className={'givewp-options-list--item--draggable'} {...provided.dragHandleProps}>
                <Icon icon={draggable} />
            </span>
            <Tooltip
                text={defaultTooltip ? defaultTooltip : __('Default', 'give')}
                position="top"
                placement="top"
                delay={500}
            >
                {/* div is required (for some reason) for the Tooltip to work, do not remove */}
                <div>
                    {selectable && (
                        <input
                            type={multiple ? 'checkbox' : 'radio'}
                            checked={option.checked}
                            className={'givewp-options-list--item--checked'}
                            onClick={() => handleUpdateOptionChecked(!option.checked)}
                        />
                    )}
                </div>
            </Tooltip>
            <div
                className={cn('givewp-options-list--item--inputs', {
                    ['givewp-options-list--item--inputs--open']: showValues,
                })}
            >
                {currency ? (
                    <CurrencyControl
                        label={__('Donation amount level', 'give')}
                        hideLabelFromVision
                        value={option.value}
                        onValueChange={(value) => {
                            handleUpdateOptionLabel(value);
                            handleUpdateOptionValue(value);
                        }}
                    />
                ) : (
                    <>
                        <input
                            type={'text'}
                            value={option.label}
                            placeholder={__('Label', 'give')}
                            onChange={(event) => handleUpdateOptionLabel(event.target.value)}
                            readOnly={readOnly}
                        />

                        {showValues && (
                            <input
                                type={'text'}
                                value={option.value}
                                placeholder={__('Value', 'give')}
                                onChange={(event) => handleUpdateOptionValue(event.target.value)}
                                readOnly={readOnly}
                            />
                        )}
                    </>
                )}
            </div>
            {!readOnly && (
                <Button
                    icon={minusCircle}
                    className={'givewp-options-list--item--button'}
                    onClick={() => handleRemoveOption()}
                />
            )}
        </div>
    );
}
