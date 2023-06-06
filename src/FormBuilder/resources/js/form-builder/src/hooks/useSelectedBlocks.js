import {useSelect} from '@wordpress/data';
import {store as blockEditorStore} from '@wordpress/block-editor';

const useSelectedBlocks = () => {
    const {selectedBlocks} = useSelect((select) => {
        const {getSelectedBlockClientIds} = select(blockEditorStore);
        return {
            selectedBlocks: getSelectedBlockClientIds(),
        };
    });

    return selectedBlocks;
};

export default useSelectedBlocks;
