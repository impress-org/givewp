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
}: OptionsListProps) {
    const handleRemoveOption = (index: number) => (): void => {
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
                        {options.map((option: OptionProps, index: number) => (
                            <Draggable key={index} draggableId={`option-${index}`} index={index}>
                                {(provided) => (
                                    <OptionsItem
                                        {...{
                                            currency,
                                            provided,
                                            option,
                                            index,
                                            showValues,
                                            multiple,
                                            selectable,
                                            defaultTooltip: defaultControlsTooltip,
                                            handleRemoveOption: handleRemoveOption(index),
                                            handleUpdateOptionLabel: handleUpdateOptionLabel(index),
                                            handleUpdateOptionValue: handleUpdateOptionValue(index),
                                            handleUpdateOptionChecked: handleUpdateOptionChecked(index, multiple),
                                        }}
                                    />
                                )}
                            </Draggable>
                        ))}
                        {provided.placeholder}
                    </div>
                )}
            </Droppable>
        </DragDropContext>
    );
}
