import styles from '@givewp/components/ListTable/ReorderableList/style.module.scss';
import {useState} from 'react';
import HamburgerIcon from '@givewp/components/ListTable/Icons/HamburgerIcon';
import ReorderableCheckbox, {ReorderableCheckboxProps} from './ReorderableCheckbox';

interface ReorderableListProps {
    columns: Array<object>;
    dragAndDropData: Array<object>;
    setDragAndDropData: (reOrderedList: Array<object>) => void;
    reorderableCheckboxRefs;
}

const ReorderableListHeader = ({restoreDefault}) => {
    return (
        <div className={styles.header}>
            <h6>Reorder the columns below.</h6>
            <button onClick={restoreDefault}>Restore default</button>
        </div>
    );
};

const ReorderableList = ({columns, dragAndDropData, setDragAndDropData, reorderableCheckboxRefs}: ReorderableListProps) => {
    const [dragPosition, setDragPosition] = useState<number>(null);
    const [dragOverPosition, setDragOverPosition] = useState<number>(null);
    const [dragItemData, setDragItemData] = useState<object>();


    const restoreDefault = (event) => {
        event.preventDefault();
        setDragAndDropData(columns);
    };

    const handleDragStart = (event, position) => {
        setDragPosition(position);
        setDragItemData(dragAndDropData[position]);
    };

    const handleDragOver = (event, position) => {
        setDragOverPosition(position);
    };

    const handleDrop = () => {
        const reOrderedList = [...dragAndDropData];
        reOrderedList.splice(dragPosition, 1);
        reOrderedList.splice(dragOverPosition, 0, dragItemData);
        setDragPosition(null);
        setDragOverPosition(null);
        setDragItemData(null);
        setDragAndDropData(reOrderedList);
    };

    return (
        <>
            <ReorderableListHeader restoreDefault={(event) => restoreDefault(event)} />
            <ul className={styles.draggableList} role="document">
                {dragAndDropData.map((column: ReorderableCheckboxProps, index) => {
                    return (
                        <li
                            key={column.id}
                            className={styles.draggableItem}
                            draggable={true}
                            onDragStart={(event) => handleDragStart(event, index)}
                            onDragEnter={(event) => handleDragOver(event, index)}
                            onDragOver={(event) => event.preventDefault()}
                            onDragEnd={handleDrop}
                        >
                            <ReorderableCheckbox
                                reorderableCheckboxRefs={reorderableCheckboxRefs}
                                id={column.id}
                                label={column.label}
                                visible={column.visible}
                            />
                            <HamburgerIcon />
                        </li>
                    );
                })}
            </ul>
        </>
    );
};

export default ReorderableList;
