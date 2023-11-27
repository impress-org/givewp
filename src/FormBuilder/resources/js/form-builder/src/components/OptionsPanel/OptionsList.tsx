import {DragDropContext, Draggable, Droppable} from 'react-beautiful-dnd';

import OptionsItem from './OptionsItem';
import {OptionProps, OptionsListProps} from './types';

export default function OptionsList({
    currency,
    options,
    showValues,
    multiple,
    selectable,
    setOptions,
    defaultControlsTooltip,
    onRemoveOption,
    readOnly,
}: OptionsListProps) {
    const handleRemoveOption = (index: number) => (): void => {
        if (onRemoveOption) {
            onRemoveOption(options[index], index);
            return;
        }

        options[index].label = '';
        options[index].value = '';
        setOptions(options.filter((option, optionIndex) => optionIndex !== index));
    };

    const handleUpdateOptionLabel =
        (index: number) =>
        (label: string): void => {
            const updatedOptions = [...options];

            updatedOptions[index].label = label;
            setOptions(updatedOptions);
        };

    const handleUpdateOptionValue =
        (index: number) =>
        (value: string): void => {
            const updatedOptions = [...options];
            updatedOptions[index].value = value;
            setOptions(updatedOptions);
        };

    const handleUpdateOptionChecked =
        (index: number, multiple: boolean) =>
        (checked: boolean): void => {
            // bail if we're trying to uncheck a single option
            if (!multiple && options[index].checked) {
                return;
            }

            const updatedOptions = [...options];
            if (!multiple) {
                updatedOptions.forEach((option, optionIndex) => {
                    if (optionIndex !== index) {
                        option.checked = false;
                    }
                });
            }
            updatedOptions[index].checked = checked;
            setOptions(updatedOptions);
        };

    const handleUpdateOptionsOrder = (result) => {
        if (!result.destination) {
            return;
        }

        const updatedOptions = [...options];
        const [reorderedItem] = updatedOptions.splice(result.source.index, 1);
        updatedOptions.splice(result.destination.index, 0, reorderedItem);
        setOptions(updatedOptions);
    };

    return (
        <DragDropContext onDragEnd={handleUpdateOptionsOrder}>
            <Droppable droppableId="options">
                {(provided) => (
                    <div {...provided.droppableProps} ref={provided.innerRef}>
                        {options.map((option: OptionProps, index: number) => {
                            const key = option.id ? option.id : index;
                            return (
                                <Draggable key={key} draggableId={`option-${key}`} index={index}>
                                    {(provided) => (
                                        <OptionsItem
                                            currency={currency}
                                            provided={provided}
                                            option={option}
                                            showValues={showValues}
                                            multiple={multiple}
                                            selectable={selectable}
                                            defaultTooltip={defaultControlsTooltip}
                                            handleRemoveOption={handleRemoveOption(index)}
                                            handleUpdateOptionLabel={handleUpdateOptionLabel(index)}
                                            handleUpdateOptionValue={handleUpdateOptionValue(index)}
                                            handleUpdateOptionChecked={handleUpdateOptionChecked(index, multiple)}
                                            readOnly={readOnly}
                                        />
                                    )}
                                </Draggable>
                            );
                        })}
                        {provided.placeholder}
                    </div>
                )}
            </Droppable>
        </DragDropContext>
    );
}
